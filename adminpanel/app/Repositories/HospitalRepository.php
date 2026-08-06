<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\HospitalRepositoryInterface;
use App\Core\Model;
use App\Models\Hospital;
use PDO;

final class HospitalRepository extends Model implements HospitalRepositoryInterface
{
    public function paginate(array $filters, int $page, int $perPage, string $sort, string $direction): array
    {
        [$where, $params] = $this->buildFilters($filters);
        $sort = in_array($sort, Hospital::SORTABLE, true) ? $sort : 'created_at';
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        $offset = max(0, ($page - 1) * $perPage);

        $sql = "SELECT h.* FROM hospitals h {$where} ORDER BY h.{$sort} {$direction} LIMIT :limit OFFSET :offset";
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
        $stmt = self::db()->prepare("SELECT COUNT(*) FROM hospitals h {$where}");
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function findById(int $id, bool $withTrashed = false): ?array
    {
        $sql = 'SELECT * FROM hospitals WHERE id = :id' . ($withTrashed ? '' : ' AND deleted_at IS NULL') . ' LIMIT 1';
        $stmt = self::db()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findBySlug(string $slug, bool $withTrashed = false): ?array
    {
        $sql = 'SELECT * FROM hospitals WHERE slug = :slug' . ($withTrashed ? '' : ' AND deleted_at IS NULL') . ' LIMIT 1';
        $stmt = self::db()->prepare($sql);
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT 1 FROM hospitals WHERE slug = :slug';
        $params = ['slug' => $slug];
        if ($ignoreId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $ignoreId;
        }
        $stmt = self::db()->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }

    public function nameExists(string $name, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT 1 FROM hospitals WHERE LOWER(name) = LOWER(:name) AND deleted_at IS NULL';
        $params = ['name' => $name];
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
        $sql = sprintf('INSERT INTO hospitals (%s) VALUES (%s)', implode(', ', $columns), implode(', ', $placeholders));
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
        self::db()->prepare('UPDATE hospitals SET ' . implode(', ', $sets) . ' WHERE id = :id')->execute($data);
    }

    public function softDelete(int $id): void
    {
        self::db()->prepare(
            "UPDATE hospitals SET deleted_at = NOW(3), status = 'archived', updated_at = NOW(3) WHERE id = :id AND deleted_at IS NULL"
        )->execute(['id' => $id]);
    }

    public function restore(int $id): void
    {
        self::db()->prepare(
            "UPDATE hospitals SET deleted_at = NULL, status = 'draft', updated_at = NOW(3) WHERE id = :id AND deleted_at IS NOT NULL"
        )->execute(['id' => $id]);
    }

    public function bulkSoftDelete(array $ids): int
    {
        if ($ids === []) {
            return 0;
        }
        $in = $this->inClause($ids);
        $stmt = self::db()->prepare(
            "UPDATE hospitals SET deleted_at = NOW(3), status = 'archived', updated_at = NOW(3)
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
            "UPDATE hospitals SET deleted_at = NULL, status = 'draft', updated_at = NOW(3) WHERE id IN ({$in['sql']}) AND deleted_at IS NOT NULL"
        );
        $stmt->execute($in['params']);

        return $stmt->rowCount();
    }

    public function bulkUpdateStatus(array $ids, string $status): int
    {
        if ($ids === [] || !in_array($status, Hospital::STATUSES, true)) {
            return 0;
        }
        $in = $this->inClause($ids);
        $params = $in['params'];
        $params['status'] = $status;
        $stmt = self::db()->prepare(
            "UPDATE hospitals SET status = :status, updated_at = NOW(3)
             WHERE deleted_at IS NULL AND id IN ({$in['sql']})"
        );
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    public function syncAccreditations(int $hospitalId, array $codes): void
    {
        self::db()->prepare('DELETE FROM hospital_accreditation WHERE hospital_id = :hospital_id')
            ->execute(['hospital_id' => $hospitalId]);
        $codes = array_values(array_unique(array_filter(array_map('strval', $codes))));
        $allowed = array_keys(Hospital::ACCREDITATIONS);
        $stmt = self::db()->prepare(
            'INSERT INTO hospital_accreditation (hospital_id, code, created_at) VALUES (:hospital_id, :code, NOW(3))'
        );
        foreach ($codes as $code) {
            if (!in_array($code, $allowed, true)) {
                continue;
            }
            $stmt->execute(['hospital_id' => $hospitalId, 'code' => $code]);
        }
    }

    public function syncInternationalServices(int $hospitalId, array $serviceCodes): void
    {
        self::db()->prepare('DELETE FROM hospital_international_service WHERE hospital_id = :hospital_id')
            ->execute(['hospital_id' => $hospitalId]);
        $serviceCodes = array_values(array_unique(array_filter(array_map('strval', $serviceCodes))));
        $allowed = array_keys(Hospital::INTERNATIONAL_SERVICES);
        $stmt = self::db()->prepare(
            'INSERT INTO hospital_international_service (hospital_id, service_code, created_at)
             VALUES (:hospital_id, :service_code, NOW(3))'
        );
        foreach ($serviceCodes as $code) {
            if (!in_array($code, $allowed, true)) {
                continue;
            }
            $stmt->execute(['hospital_id' => $hospitalId, 'service_code' => $code]);
        }
    }

    public function syncTreatments(int $hospitalId, array $treatmentIds): void
    {
        self::db()->prepare('DELETE FROM treatment_hospital WHERE hospital_id = :hospital_id')
            ->execute(['hospital_id' => $hospitalId]);
        $treatmentIds = array_values(array_unique(array_filter(array_map('intval', $treatmentIds))));
        if ($treatmentIds === []) {
            return;
        }
        $stmt = self::db()->prepare(
            'INSERT INTO treatment_hospital (treatment_id, hospital_id, created_at)
             VALUES (:treatment_id, :hospital_id, NOW(3))'
        );
        foreach ($treatmentIds as $treatmentId) {
            $stmt->execute(['treatment_id' => $treatmentId, 'hospital_id' => $hospitalId]);
        }
    }

    public function accreditationCodes(int $hospitalId): array
    {
        $map = $this->accreditationCodesByHospitalIds([$hospitalId]);

        return $map[$hospitalId] ?? [];
    }

    public function internationalServiceCodes(int $hospitalId): array
    {
        $map = $this->internationalServiceCodesByHospitalIds([$hospitalId]);

        return $map[$hospitalId] ?? [];
    }

    /**
     * @param list<int> $hospitalIds
     * @return array<int, list<string>>
     */
    public function accreditationCodesByHospitalIds(array $hospitalIds): array
    {
        return $this->codesGroupedByHospital(
            $hospitalIds,
            'hospital_accreditation',
            'code'
        );
    }

    /**
     * @param list<int> $hospitalIds
     * @return array<int, list<string>>
     */
    public function internationalServiceCodesByHospitalIds(array $hospitalIds): array
    {
        return $this->codesGroupedByHospital(
            $hospitalIds,
            'hospital_international_service',
            'service_code'
        );
    }

    /**
     * @param list<int> $hospitalIds
     * @return array<int, list<string>>
     */
    private function codesGroupedByHospital(array $hospitalIds, string $table, string $codeColumn): array
    {
        $hospitalIds = array_values(array_unique(array_filter(
            array_map('intval', $hospitalIds),
            static fn (int $id): bool => $id > 0
        )));
        $result = [];
        foreach ($hospitalIds as $id) {
            $result[$id] = [];
        }
        if ($hospitalIds === []) {
            return $result;
        }

        $placeholders = [];
        $params = [];
        foreach ($hospitalIds as $i => $id) {
            $key = 'id' . $i;
            $placeholders[] = ':' . $key;
            $params[$key] = $id;
        }

        $sql = sprintf(
            'SELECT hospital_id, %s AS code FROM %s WHERE hospital_id IN (%s)',
            $codeColumn,
            $table,
            implode(', ', $placeholders)
        );
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $hid = (int) $row['hospital_id'];
            $result[$hid][] = (string) $row['code'];
        }

        return $result;
    }

    public function treatmentIds(int $hospitalId): array
    {
        $stmt = self::db()->prepare('SELECT treatment_id FROM treatment_hospital WHERE hospital_id = :hospital_id');
        $stmt->execute(['hospital_id' => $hospitalId]);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
    }

    public function relatedTreatments(int $hospitalId): array
    {
        try {
            $stmt = self::db()->prepare(
                "SELECT t.id, t.uuid, t.slug, t.name, t.status, t.featured_image
                 FROM treatments t
                 INNER JOIN treatment_hospital th ON th.treatment_id = t.id
                 WHERE th.hospital_id = :hospital_id
                   AND t.status = 'active'
                   AND t.deleted_at IS NULL
                 ORDER BY t.name ASC"
            );
            $stmt->execute(['hospital_id' => $hospitalId]);
        } catch (\Throwable) {
            $stmt = self::db()->prepare(
                "SELECT t.id, t.uuid, t.slug, t.name, t.status, t.featured_image
                 FROM treatments t
                 INNER JOIN treatment_hospital th ON th.treatment_id = t.id
                 WHERE th.hospital_id = :hospital_id AND t.status = 'active'
                 ORDER BY t.name ASC"
            );
            $stmt->execute(['hospital_id' => $hospitalId]);
        }

        return $stmt->fetchAll() ?: [];
    }

    public function relatedSpecialties(int $hospitalId): array
    {
        $stmt = self::db()->prepare(
            "SELECT DISTINCT s.id, s.slug, s.name
             FROM specialties s
             INNER JOIN doctor_specialty ds ON ds.specialty_id = s.id
             INNER JOIN doctor_hospital dh ON dh.doctor_id = ds.doctor_id
             INNER JOIN doctors d ON d.id = dh.doctor_id
             WHERE dh.hospital_id = :hospital_id
               AND s.status = 'active'
               AND s.deleted_at IS NULL
               AND d.status = 'active'
               AND d.deleted_at IS NULL
             ORDER BY s.name ASC"
        );
        $stmt->execute(['hospital_id' => $hospitalId]);

        return $stmt->fetchAll() ?: [];
    }

    public function featuredDoctors(int $hospitalId, int $limit = 6): array
    {
        $limit = max(1, min(24, $limit));
        $featured = $this->doctorsForHospital($hospitalId, true, $limit);
        if (count($featured) >= $limit) {
            return array_slice($featured, 0, $limit);
        }

        $excludeIds = array_map(static fn (array $row): int => (int) $row['id'], $featured);
        $remaining = $limit - count($featured);
        $others = $this->doctorsForHospital($hospitalId, false, $remaining, $excludeIds);

        return array_values(array_merge($featured, $others));
    }

    public function exportRows(array $filters): array
    {
        [$where, $params] = $this->buildFilters($filters);
        $stmt = self::db()->prepare("SELECT h.* FROM hospitals h {$where} ORDER BY h.name ASC");
        $stmt->execute($params);

        return $stmt->fetchAll() ?: [];
    }

    /**
     * @param list<int> $excludeIds
     * @return list<array<string, mixed>>
     */
    private function doctorsForHospital(int $hospitalId, bool $featuredOnly, int $limit, array $excludeIds = []): array
    {
        $sql = "SELECT d.id, d.uuid, d.slug, d.name, d.photo, d.qualification, d.expertise,
                       d.is_featured, d.status
                FROM doctors d
                INNER JOIN doctor_hospital dh ON dh.doctor_id = d.id
                WHERE dh.hospital_id = :hospital_id
                  AND d.status = 'active'
                  AND d.deleted_at IS NULL";
        $params = ['hospital_id' => $hospitalId];

        if ($featuredOnly) {
            $sql .= ' AND d.is_featured = 1';
        }
        if ($excludeIds !== []) {
            $in = $this->inClause($excludeIds, 'ex');
            $sql .= " AND d.id NOT IN ({$in['sql']})";
            $params = array_merge($params, $in['params']);
        }

        $sql .= ' ORDER BY d.name ASC LIMIT ' . (int) $limit;
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll() ?: [];
    }

    private function buildFilters(array $filters): array
    {
        $clauses = [];
        $params = [];
        $trashed = $filters['trashed'] ?? 'active';

        if ($trashed === 'only') {
            $clauses[] = 'h.deleted_at IS NOT NULL';
        } elseif ($trashed !== 'with') {
            $clauses[] = 'h.deleted_at IS NULL';
        }

        if (!empty($filters['q'])) {
            $clauses[] = '(h.name LIKE :q_name OR h.slug LIKE :q_slug OR h.city LIKE :q_city OR h.country LIKE :q_country OR h.description LIKE :q_desc OR h.hospital_type LIKE :q_type)';
            $like = '%' . $filters['q'] . '%';
            $params['q_name'] = $like;
            $params['q_slug'] = $like;
            $params['q_city'] = $like;
            $params['q_country'] = $like;
            $params['q_desc'] = $like;
            $params['q_type'] = $like;
        }
        if (!empty($filters['status']) && in_array($filters['status'], Hospital::STATUSES, true)) {
            $clauses[] = 'h.status = :status';
            $params['status'] = $filters['status'];
        }
        if (isset($filters['is_featured']) && $filters['is_featured'] !== '') {
            $clauses[] = 'h.is_featured = :is_featured';
            $params['is_featured'] = (int) $filters['is_featured'];
        }
        if (isset($filters['is_verified']) && $filters['is_verified'] !== '') {
            $clauses[] = 'h.is_verified = :is_verified';
            $params['is_verified'] = (int) $filters['is_verified'];
        }
        if (!empty($filters['hospital_type'])) {
            $clauses[] = 'h.hospital_type = :hospital_type';
            $params['hospital_type'] = (string) $filters['hospital_type'];
        }
        if (!empty($filters['country'])) {
            $clauses[] = 'h.country = :country';
            $params['country'] = (string) $filters['country'];
        }
        if (!empty($filters['city'])) {
            $clauses[] = 'h.city = :city';
            $params['city'] = (string) $filters['city'];
        }
        if (!empty($filters['accreditation'])) {
            $clauses[] = 'EXISTS (SELECT 1 FROM hospital_accreditation ha WHERE ha.hospital_id = h.id AND ha.code = :accreditation)';
            $params['accreditation'] = (string) $filters['accreditation'];
        }
        if (!empty($filters['treatment_id'])) {
            $clauses[] = 'EXISTS (SELECT 1 FROM treatment_hospital th WHERE th.hospital_id = h.id AND th.treatment_id = :treatment_id)';
            $params['treatment_id'] = (int) $filters['treatment_id'];
        }

        return [$clauses === [] ? '' : 'WHERE ' . implode(' AND ', $clauses), $params];
    }

    private function inClause(array $ids, string $prefix = 'id'): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        $parts = [];
        $params = [];
        foreach ($ids as $i => $id) {
            $key = $prefix . $i;
            $parts[] = ':' . $key;
            $params[$key] = $id;
        }

        return ['sql' => implode(',', $parts), 'params' => $params];
    }
}
