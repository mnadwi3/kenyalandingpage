<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\SpecialtyRepositoryInterface;
use App\Core\Model;
use App\Models\Specialty;
use PDO;

final class SpecialtyRepository extends Model implements SpecialtyRepositoryInterface
{
    public function paginate(array $filters, int $page, int $perPage, string $sort, string $direction): array
    {
        [$where, $params] = $this->buildFilters($filters);
        $sort = in_array($sort, Specialty::SORTABLE, true) ? $sort : 'created_at';
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        $offset = max(0, ($page - 1) * $perPage);

        $sql = "SELECT s.* FROM specialties s {$where} ORDER BY s.{$sort} {$direction} LIMIT :limit OFFSET :offset";
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
        $stmt = self::db()->prepare("SELECT COUNT(*) FROM specialties s {$where}");
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function findById(int $id, bool $withTrashed = false): ?array
    {
        $sql = 'SELECT * FROM specialties WHERE id = :id' . ($withTrashed ? '' : ' AND deleted_at IS NULL') . ' LIMIT 1';
        $stmt = self::db()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT 1 FROM specialties WHERE slug = :slug';
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
        $sql = 'SELECT 1 FROM specialties WHERE name = :name';
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
        $sql = sprintf('INSERT INTO specialties (%s) VALUES (%s)', implode(', ', $columns), implode(', ', $placeholders));
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
        self::db()->prepare('UPDATE specialties SET ' . implode(', ', $sets) . ' WHERE id = :id')->execute($data);
    }

    public function softDelete(int $id): void
    {
        self::db()->prepare(
            "UPDATE specialties SET deleted_at = NOW(3), status = 'archived', updated_at = NOW(3) WHERE id = :id AND deleted_at IS NULL"
        )->execute(['id' => $id]);
    }

    public function restore(int $id): void
    {
        self::db()->prepare(
            "UPDATE specialties SET deleted_at = NULL, status = 'draft', updated_at = NOW(3) WHERE id = :id"
        )->execute(['id' => $id]);
    }

    public function bulkSoftDelete(array $ids): int
    {
        if ($ids === []) {
            return 0;
        }
        $in = $this->inClause($ids);
        $stmt = self::db()->prepare(
            "UPDATE specialties SET deleted_at = NOW(3), status = 'archived', updated_at = NOW(3)
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
            "UPDATE specialties SET deleted_at = NULL, status = 'draft', updated_at = NOW(3) WHERE id IN ({$in['sql']})"
        );
        $stmt->execute($in['params']);

        return $stmt->rowCount();
    }

    public function bulkUpdateStatus(array $ids, string $status): int
    {
        if ($ids === [] || !in_array($status, Specialty::STATUSES, true)) {
            return 0;
        }
        $in = $this->inClause($ids);
        $params = $in['params'];
        $params['status'] = $status;
        $stmt = self::db()->prepare(
            "UPDATE specialties SET status = :status, updated_at = NOW(3)
             WHERE deleted_at IS NULL AND id IN ({$in['sql']})"
        );
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    public function exportRows(array $filters): array
    {
        [$where, $params] = $this->buildFilters($filters);
        $stmt = self::db()->prepare("SELECT s.* FROM specialties s {$where} ORDER BY s.name ASC");
        $stmt->execute($params);

        return $stmt->fetchAll() ?: [];
    }

    private function buildFilters(array $filters): array
    {
        $clauses = [];
        $params = [];
        $trashed = $filters['trashed'] ?? 'active';

        if ($trashed === 'only') {
            $clauses[] = 's.deleted_at IS NOT NULL';
        } elseif ($trashed !== 'with') {
            $clauses[] = 's.deleted_at IS NULL';
        }

        if (!empty($filters['q'])) {
            $clauses[] = '(s.name LIKE :q_name OR s.slug LIKE :q_slug)';
            $like = '%' . $filters['q'] . '%';
            $params['q_name'] = $like;
            $params['q_slug'] = $like;
        }
        if (!empty($filters['status']) && in_array($filters['status'], Specialty::STATUSES, true)) {
            $clauses[] = 's.status = :status';
            $params['status'] = $filters['status'];
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
