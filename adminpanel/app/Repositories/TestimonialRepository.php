<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Model;
use App\Models\Testimonial;
use PDO;

final class TestimonialRepository extends Model
{
    public function paginate(array $filters, int $page, int $perPage, string $sort, string $direction): array
    {
        [$where, $params] = $this->buildFilters($filters);
        $sort = in_array($sort, Testimonial::SORTABLE, true) ? $sort : 'sort_order';
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $offset = max(0, ($page - 1) * $perPage);

        $stmt = self::db()->prepare(
            "SELECT * FROM testimonials {$where} ORDER BY {$sort} {$direction} LIMIT :limit OFFSET :offset"
        );
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
        $stmt = self::db()->prepare("SELECT COUNT(*) FROM testimonials {$where}");
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function findById(int $id, bool $withTrashed = false): ?array
    {
        $sql = 'SELECT * FROM testimonials WHERE id = :id' . ($withTrashed ? '' : ' AND deleted_at IS NULL') . ' LIMIT 1';
        $stmt = self::db()->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function active(): array
    {
        $stmt = self::db()->query(
            "SELECT * FROM testimonials WHERE status = 'active' AND deleted_at IS NULL ORDER BY sort_order ASC, created_at DESC"
        );

        return $stmt->fetchAll() ?: [];
    }

    public function create(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(static fn (string $c): string => ':' . $c, $columns);
        self::db()->prepare(
            sprintf('INSERT INTO testimonials (%s) VALUES (%s)', implode(', ', $columns), implode(', ', $placeholders))
        )->execute($data);

        return (int) self::db()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $sets = [];
        foreach (array_keys($data) as $column) {
            $sets[] = "{$column} = :{$column}";
        }
        $data['id'] = $id;
        self::db()->prepare('UPDATE testimonials SET ' . implode(', ', $sets) . ' WHERE id = :id')->execute($data);
    }

    public function softDelete(int $id): void
    {
        self::db()->prepare(
            "UPDATE testimonials SET deleted_at = NOW(3), status = 'archived', updated_at = NOW(3) WHERE id = :id AND deleted_at IS NULL"
        )->execute(['id' => $id]);
    }

    public function restore(int $id): void
    {
        self::db()->prepare(
            "UPDATE testimonials SET deleted_at = NULL, status = 'draft', updated_at = NOW(3) WHERE id = :id AND deleted_at IS NOT NULL"
        )->execute(['id' => $id]);
    }

    private function buildFilters(array $filters): array
    {
        $clauses = [];
        $params = [];
        $trashed = $filters['trashed'] ?? 'active';

        if ($trashed === 'only') {
            $clauses[] = 'deleted_at IS NOT NULL';
        } elseif ($trashed !== 'with') {
            $clauses[] = 'deleted_at IS NULL';
        }

        if (!empty($filters['q'])) {
            $clauses[] = '(patient_name LIKE :q_name OR treatment_name LIKE :q_treatment)';
            $like = '%' . $filters['q'] . '%';
            $params['q_name'] = $like;
            $params['q_treatment'] = $like;
        }
        if (!empty($filters['status']) && in_array($filters['status'], Testimonial::STATUSES, true)) {
            $clauses[] = 'status = :status';
            $params['status'] = $filters['status'];
        }

        return [$clauses === [] ? '' : 'WHERE ' . implode(' AND ', $clauses), $params];
    }
}
