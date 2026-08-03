<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\DoctorRepositoryInterface;
use App\Core\Model;
use App\Models\Doctor;
use PDO;

final class DoctorRepository extends Model implements DoctorRepositoryInterface
{
    public function paginate(array $filters, int $page, int $perPage, string $sort, string $direction): array
    {
        [$where, $params] = $this->buildFilters($filters);
        $sort = in_array($sort, Doctor::SORTABLE, true) ? $sort : 'created_at';
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        $offset = max(0, ($page - 1) * $perPage);

        $sql = "SELECT d.* FROM doctors d {$where} ORDER BY d.{$sort} {$direction} LIMIT :limit OFFSET :offset";
        $stmt = self::db()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    public function countFiltered(array $filters): int
    {
        [$where, $params] = $this->buildFilters($filters);
        $stmt = self::db()->prepare("SELECT COUNT(*) FROM doctors d {$where}");
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function findById(int $id, bool $withTrashed = false): ?array
    {
        $sql = 'SELECT * FROM doctors WHERE id = :id' . ($withTrashed ? '' : ' AND deleted_at IS NULL') . ' LIMIT 1';
        $stmt = self::db()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findBySlug(string $slug, bool $withTrashed = false): ?array
    {
        $sql = 'SELECT * FROM doctors WHERE slug = :slug' . ($withTrashed ? '' : ' AND deleted_at IS NULL') . ' LIMIT 1';
        $stmt = self::db()->prepare($sql);
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT 1 FROM doctors WHERE slug = :slug';
        $params = ['slug' => $slug];
        if ($ignoreId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $ignoreId;
        }
        $stmt = self::db()->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(static fn (string $c): string => ':' . $c, $columns);
        $sql = sprintf('INSERT INTO doctors (%s) VALUES (%s)', implode(', ', $columns), implode(', ', $placeholders));
        self::db()->prepare($sql)->execute($data);

        return (int) self::db()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $sets = [];
        foreach (array_keys($data) as $column) {
            $sets[] = "{$column} = :{$column}";
        }
        $data['id'] = $id;
        self::db()->prepare('UPDATE doctors SET ' . implode(', ', $sets) . ' WHERE id = :id')->execute($data);
    }

    public function softDelete(int $id): void
    {
        self::db()->prepare(
            "UPDATE doctors SET deleted_at = NOW(3), status = 'archived', updated_at = NOW(3) WHERE id = :id AND deleted_at IS NULL"
        )->execute(['id' => $id]);
    }

    public function restore(int $id): void
    {
        self::db()->prepare(
            "UPDATE doctors SET deleted_at = NULL, status = 'draft', updated_at = NOW(3) WHERE id = :id"
        )->execute(['id' => $id]);
    }

    public function bulkSoftDelete(array $ids): int
    {
        if ($ids === []) {
            return 0;
        }
        $in = $this->inClause($ids);
        $stmt = self::db()->prepare(
            "UPDATE doctors SET deleted_at = NOW(3), status = 'archived', updated_at = NOW(3)
             WHERE deleted_at IS NULL AND id IN ({$in['sql']})"
        );
        $stmt->execute($in['params']);

        return $stmt->rowCount();
    }

    public function bulkRestore(array $ids): int
    {
        if ($ids === []) {
            return 0;
        }
        $in = $this->inClause($ids);
        $stmt = self::db()->prepare(
            "UPDATE doctors SET deleted_at = NULL, status = 'draft', updated_at = NOW(3) WHERE id IN ({$in['sql']})"
        );
        $stmt->execute($in['params']);

        return $stmt->rowCount();
    }

    public function bulkUpdateStatus(array $ids, string $status): int
    {
        if ($ids === [] || !in_array($status, Doctor::STATUSES, true)) {
            return 0;
        }
        $in = $this->inClause($ids);
        $params = $in['params'];
        $params['status'] = $status;
        $stmt = self::db()->prepare(
            "UPDATE doctors SET status = :status, updated_at = NOW(3)
             WHERE deleted_at IS NULL AND id IN ({$in['sql']})"
        );
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    public function syncLanguages(int $doctorId, array $languageIds): void
    {
        $this->syncJunction('doctor_language', 'language_id', $doctorId, $languageIds);
    }

    public function syncSpecialties(int $doctorId, array $specialtyIds): void
    {
        $this->syncJunction('doctor_specialty', 'specialty_id', $doctorId, $specialtyIds);
    }

    public function syncTreatments(int $doctorId, array $treatmentIds): void
    {
        $this->syncJunction('doctor_treatment', 'treatment_id', $doctorId, $treatmentIds);
    }

    public function syncHospitals(int $doctorId, array $hospitalIds): void
    {
        $this->syncJunction('doctor_hospital', 'hospital_id', $doctorId, $hospitalIds);
    }

    public function languageIds(int $doctorId): array
    {
        return $this->junctionIds('doctor_language', 'language_id', $doctorId);
    }

    public function specialtyIds(int $doctorId): array
    {
        return $this->junctionIds('doctor_specialty', 'specialty_id', $doctorId);
    }

    public function treatmentIds(int $doctorId): array
    {
        return $this->junctionIds('doctor_treatment', 'treatment_id', $doctorId);
    }

    public function hospitalIds(int $doctorId): array
    {
        return $this->junctionIds('doctor_hospital', 'hospital_id', $doctorId);
    }

    public function exportRows(array $filters): array
    {
        [$where, $params] = $this->buildFilters($filters);
        $stmt = self::db()->prepare("SELECT d.* FROM doctors d {$where} ORDER BY d.name ASC");
        $stmt->execute($params);

        return $stmt->fetchAll() ?: [];
    }

    private function buildFilters(array $filters): array
    {
        $clauses = [];
        $params = [];
        $trashed = $filters['trashed'] ?? 'active';

        if ($trashed === 'only') {
            $clauses[] = 'd.deleted_at IS NOT NULL';
        } elseif ($trashed !== 'with') {
            $clauses[] = 'd.deleted_at IS NULL';
        }

        if (!empty($filters['q'])) {
            $clauses[] = '(d.name LIKE :q_name OR d.qualification LIKE :q_qual OR d.expertise LIKE :q_expertise OR d.slug LIKE :q_slug)';
            $like = '%' . $filters['q'] . '%';
            $params['q_name'] = $like;
            $params['q_qual'] = $like;
            $params['q_expertise'] = $like;
            $params['q_slug'] = $like;
        }
        if (!empty($filters['status']) && in_array($filters['status'], Doctor::STATUSES, true)) {
            $clauses[] = 'd.status = :status';
            $params['status'] = $filters['status'];
        }
        if (isset($filters['is_featured']) && $filters['is_featured'] !== '') {
            $clauses[] = 'd.is_featured = :is_featured';
            $params['is_featured'] = (int) $filters['is_featured'];
        }
        if (!empty($filters['specialty_id'])) {
            $clauses[] = 'EXISTS (SELECT 1 FROM doctor_specialty ds WHERE ds.doctor_id = d.id AND ds.specialty_id = :specialty_id)';
            $params['specialty_id'] = (int) $filters['specialty_id'];
        }
        if (!empty($filters['hospital_id'])) {
            $clauses[] = 'EXISTS (SELECT 1 FROM doctor_hospital dh WHERE dh.doctor_id = d.id AND dh.hospital_id = :hospital_id)';
            $params['hospital_id'] = (int) $filters['hospital_id'];
        }
        if (!empty($filters['treatment_id'])) {
            $clauses[] = 'EXISTS (SELECT 1 FROM doctor_treatment dt WHERE dt.doctor_id = d.id AND dt.treatment_id = :treatment_id)';
            $params['treatment_id'] = (int) $filters['treatment_id'];
        }

        return [$clauses === [] ? '' : 'WHERE ' . implode(' AND ', $clauses), $params];
    }

    private function syncJunction(string $table, string $foreignKey, int $doctorId, array $ids): void
    {
        $allowed = ['doctor_language', 'doctor_specialty', 'doctor_treatment', 'doctor_hospital'];
        $allowedKeys = ['language_id', 'specialty_id', 'treatment_id', 'hospital_id'];
        if (!in_array($table, $allowed, true) || !in_array($foreignKey, $allowedKeys, true)) {
            return;
        }

        self::db()->prepare("DELETE FROM {$table} WHERE doctor_id = :doctor_id")->execute(['doctor_id' => $doctorId]);
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return;
        }

        $stmt = self::db()->prepare(
            "INSERT INTO {$table} (doctor_id, {$foreignKey}, created_at) VALUES (:doctor_id, :fk, NOW(3))"
        );
        foreach ($ids as $fk) {
            $stmt->execute(['doctor_id' => $doctorId, 'fk' => $fk]);
        }
    }

    private function junctionIds(string $table, string $foreignKey, int $doctorId): array
    {
        $stmt = self::db()->prepare("SELECT {$foreignKey} FROM {$table} WHERE doctor_id = :doctor_id");
        $stmt->execute(['doctor_id' => $doctorId]);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
    }

    private function inClause(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        $parts = [];
        $params = [];
        foreach ($ids as $i => $id) {
            $key = 'id' . $i;
            $parts[] = ':' . $key;
            $params[$key] = $id;
        }

        return ['sql' => implode(',', $parts), 'params' => $params];
    }
}
