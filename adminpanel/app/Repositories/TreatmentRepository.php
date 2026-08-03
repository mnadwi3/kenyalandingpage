<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\TreatmentRepositoryInterface;
use App\Core\Model;
use App\Models\Treatment;
use PDO;

final class TreatmentRepository extends Model implements TreatmentRepositoryInterface
{
    public function paginate(array $filters, int $page, int $perPage, string $sort, string $direction): array
    {
        [$where, $params] = $this->buildFilters($filters);
        $sort = in_array($sort, Treatment::SORTABLE, true) ? $sort : 'created_at';
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        $offset = max(0, ($page - 1) * $perPage);

        $sql = "SELECT t.*, s.name AS specialty_name
                FROM treatments t
                LEFT JOIN specialties s ON s.id = t.specialty_id
                {$where}
                ORDER BY t.{$sort} {$direction}
                LIMIT :limit OFFSET :offset";
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
        $stmt = self::db()->prepare("SELECT COUNT(*) FROM treatments t {$where}");
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function findById(int $id, bool $withTrashed = false): ?array
    {
        $sql = 'SELECT t.*, s.name AS specialty_name
                FROM treatments t
                LEFT JOIN specialties s ON s.id = t.specialty_id
                WHERE t.id = :id' . ($withTrashed ? '' : ' AND t.deleted_at IS NULL') . ' LIMIT 1';
        $stmt = self::db()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findBySlug(string $slug, bool $withTrashed = false): ?array
    {
        $sql = 'SELECT t.*, s.name AS specialty_name
                FROM treatments t
                LEFT JOIN specialties s ON s.id = t.specialty_id
                WHERE t.slug = :slug' . ($withTrashed ? '' : ' AND t.deleted_at IS NULL') . ' LIMIT 1';
        $stmt = self::db()->prepare($sql);
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT 1 FROM treatments WHERE slug = :slug';
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
        $sql = sprintf('INSERT INTO treatments (%s) VALUES (%s)', implode(', ', $columns), implode(', ', $placeholders));
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
        self::db()->prepare('UPDATE treatments SET ' . implode(', ', $sets) . ' WHERE id = :id')->execute($data);
    }

    public function softDelete(int $id): void
    {
        self::db()->prepare(
            "UPDATE treatments SET deleted_at = NOW(3), status = 'archived', updated_at = NOW(3) WHERE id = :id AND deleted_at IS NULL"
        )->execute(['id' => $id]);
    }

    public function restore(int $id): void
    {
        self::db()->prepare(
            "UPDATE treatments SET deleted_at = NULL, status = 'draft', updated_at = NOW(3) WHERE id = :id"
        )->execute(['id' => $id]);
    }

    public function bulkSoftDelete(array $ids): int
    {
        if ($ids === []) {
            return 0;
        }
        $in = $this->inClause($ids);
        $stmt = self::db()->prepare(
            "UPDATE treatments SET deleted_at = NOW(3), status = 'archived', updated_at = NOW(3)
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
            "UPDATE treatments SET deleted_at = NULL, status = 'draft', updated_at = NOW(3) WHERE id IN ({$in['sql']})"
        );
        $stmt->execute($in['params']);

        return $stmt->rowCount();
    }

    public function bulkUpdateStatus(array $ids, string $status): int
    {
        if ($ids === [] || !in_array($status, Treatment::STATUSES, true)) {
            return 0;
        }
        $in = $this->inClause($ids);
        $params = $in['params'];
        $params['status'] = $status;
        $stmt = self::db()->prepare(
            "UPDATE treatments SET status = :status, updated_at = NOW(3)
             WHERE deleted_at IS NULL AND id IN ({$in['sql']})"
        );
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    public function syncDoctors(int $treatmentId, array $doctorIds): void
    {
        self::db()->prepare('DELETE FROM doctor_treatment WHERE treatment_id = :treatment_id')
            ->execute(['treatment_id' => $treatmentId]);
        $doctorIds = array_values(array_unique(array_filter(array_map('intval', $doctorIds))));
        if ($doctorIds === []) {
            return;
        }
        $stmt = self::db()->prepare(
            'INSERT INTO doctor_treatment (doctor_id, treatment_id, created_at) VALUES (:doctor_id, :treatment_id, NOW(3))'
        );
        foreach ($doctorIds as $doctorId) {
            $stmt->execute(['doctor_id' => $doctorId, 'treatment_id' => $treatmentId]);
        }
    }

    public function syncHospitals(int $treatmentId, array $hospitalIds): void
    {
        self::db()->prepare('DELETE FROM treatment_hospital WHERE treatment_id = :treatment_id')
            ->execute(['treatment_id' => $treatmentId]);
        $hospitalIds = array_values(array_unique(array_filter(array_map('intval', $hospitalIds))));
        if ($hospitalIds === []) {
            return;
        }
        $stmt = self::db()->prepare(
            'INSERT INTO treatment_hospital (treatment_id, hospital_id, created_at) VALUES (:treatment_id, :hospital_id, NOW(3))'
        );
        foreach ($hospitalIds as $hospitalId) {
            $stmt->execute(['treatment_id' => $treatmentId, 'hospital_id' => $hospitalId]);
        }
    }

    public function doctorIds(int $treatmentId): array
    {
        $stmt = self::db()->prepare('SELECT doctor_id FROM doctor_treatment WHERE treatment_id = :treatment_id');
        $stmt->execute(['treatment_id' => $treatmentId]);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
    }

    public function hospitalIds(int $treatmentId): array
    {
        $stmt = self::db()->prepare('SELECT hospital_id FROM treatment_hospital WHERE treatment_id = :treatment_id');
        $stmt->execute(['treatment_id' => $treatmentId]);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
    }

    public function categories(): array
    {
        $stmt = self::db()->query(
            "SELECT DISTINCT category FROM treatments
             WHERE category IS NOT NULL AND category != '' AND deleted_at IS NULL
             ORDER BY category ASC"
        );

        return array_values(array_filter(array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [])));
    }

    public function exportRows(array $filters): array
    {
        [$where, $params] = $this->buildFilters($filters);
        $stmt = self::db()->prepare(
            "SELECT t.*, s.name AS specialty_name
             FROM treatments t
             LEFT JOIN specialties s ON s.id = t.specialty_id
             {$where}
             ORDER BY t.name ASC"
        );
        $stmt->execute($params);

        return $stmt->fetchAll() ?: [];
    }

    private function buildFilters(array $filters): array
    {
        $clauses = [];
        $params = [];
        $trashed = $filters['trashed'] ?? 'active';

        if ($trashed === 'only') {
            $clauses[] = 't.deleted_at IS NOT NULL';
        } elseif ($trashed !== 'with') {
            $clauses[] = 't.deleted_at IS NULL';
        }

        if (!empty($filters['q'])) {
            $clauses[] = '(t.name LIKE :q_name OR t.slug LIKE :q_slug OR t.category LIKE :q_category OR t.overview LIKE :q_overview)';
            $like = '%' . $filters['q'] . '%';
            $params['q_name'] = $like;
            $params['q_slug'] = $like;
            $params['q_category'] = $like;
            $params['q_overview'] = $like;
        }
        if (!empty($filters['status']) && in_array($filters['status'], Treatment::STATUSES, true)) {
            $clauses[] = 't.status = :status';
            $params['status'] = $filters['status'];
        }
        if (isset($filters['is_featured']) && $filters['is_featured'] !== '') {
            $clauses[] = 't.is_featured = :is_featured';
            $params['is_featured'] = (int) $filters['is_featured'];
        }
        if (!empty($filters['category'])) {
            $clauses[] = 't.category = :category';
            $params['category'] = (string) $filters['category'];
        }
        if (!empty($filters['specialty_id'])) {
            $clauses[] = 't.specialty_id = :specialty_id';
            $params['specialty_id'] = (int) $filters['specialty_id'];
        }
        if (!empty($filters['hospital_id'])) {
            $clauses[] = 'EXISTS (SELECT 1 FROM treatment_hospital th WHERE th.treatment_id = t.id AND th.hospital_id = :hospital_id)';
            $params['hospital_id'] = (int) $filters['hospital_id'];
        }
        if (!empty($filters['doctor_id'])) {
            $clauses[] = 'EXISTS (SELECT 1 FROM doctor_treatment dt WHERE dt.treatment_id = t.id AND dt.doctor_id = :doctor_id)';
            $params['doctor_id'] = (int) $filters['doctor_id'];
        }

        return [$clauses === [] ? '' : 'WHERE ' . implode(' AND ', $clauses), $params];
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
