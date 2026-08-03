<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\DashboardRepositoryInterface;
use App\Core\Database;
use PDO;
use Throwable;

final class DashboardRepository implements DashboardRepositoryInterface
{
    /** @var array<string, bool> */
    private array $tableCache = [];

    public function tableExists(string $table): bool
    {
        if (isset($this->tableCache[$table])) {
            return $this->tableCache[$table];
        }

        try {
            $dbName = (string) config('database.name');
            $stmt = Database::connection()->prepare(
                'SELECT 1
                 FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table
                 LIMIT 1'
            );
            $stmt->execute(['schema' => $dbName, 'table' => $table]);
            $this->tableCache[$table] = (bool) $stmt->fetchColumn();
        } catch (Throwable) {
            $this->tableCache[$table] = false;
        }

        return $this->tableCache[$table];
    }

    private const COUNTABLE = [
        'doctors',
        'treatments',
        'hospitals',
        'specialties',
        'users',
    ];

    public function countEntities(string $table, bool $activeOnly = true): int
    {
        if (!in_array($table, self::COUNTABLE, true) || !$this->tableExists($table)) {
            return 0;
        }

        try {
            $sql = "SELECT COUNT(*) FROM `{$table}`";
            if ($activeOnly) {
                $sql .= " WHERE status = 'active'";
                if (in_array($table, ['doctors', 'treatments', 'hospitals', 'specialties'], true)) {
                    $sql .= ' AND deleted_at IS NULL';
                }
            }

            return (int) Database::connection()->query($sql)->fetchColumn();
        } catch (Throwable) {
            return 0;
        }
    }

    public function recentDoctors(int $limit = 8): array
    {
        if (!$this->tableExists('doctors')) {
            return [];
        }

        try {
            $stmt = Database::connection()->prepare(
                "SELECT id, name, slug, qualification, status, is_featured, created_at, updated_at
                 FROM doctors
                 WHERE deleted_at IS NULL
                 ORDER BY updated_at DESC
                 LIMIT :limit"
            );
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll() ?: [];
        } catch (Throwable) {
            try {
                $stmt = Database::connection()->prepare(
                    'SELECT id, name, slug, qualification, status, is_featured, created_at, updated_at
                     FROM doctors
                     ORDER BY updated_at DESC
                     LIMIT :limit'
                );
                $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
                $stmt->execute();

                return $stmt->fetchAll() ?: [];
            } catch (Throwable) {
                return [];
            }
        }
    }

    public function recentLogins(int $limit = 8): array
    {
        if (!$this->tableExists('users')) {
            return [];
        }

        try {
            $stmt = Database::connection()->prepare(
                'SELECT id, name, email, last_login_at, role_id
                 FROM users
                 WHERE last_login_at IS NOT NULL
                 ORDER BY last_login_at DESC
                 LIMIT :limit'
            );
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll() ?: [];
        } catch (Throwable) {
            return [];
        }
    }
}
