<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Core\Model;
use Throwable;

final class RelatedOptionsRepository extends Model
{
    public function languages(): array
    {
        return $this->fetchOptions('languages');
    }

    public function specialties(): array
    {
        if (!$this->tableExists('specialties')) {
            return [];
        }
        try {
            return self::db()->query(
                "SELECT id, name, image FROM specialties
                 WHERE status = 'active' AND deleted_at IS NULL
                 ORDER BY name ASC"
            )->fetchAll() ?: [];
        } catch (Throwable) {
            return $this->fetchOptions('specialties');
        }
    }

    public function treatments(): array
    {
        if (!$this->tableExists('treatments')) {
            return [];
        }
        try {
            return self::db()->query(
                "SELECT id, name FROM treatments
                 WHERE status = 'active' AND deleted_at IS NULL
                 ORDER BY name ASC"
            )->fetchAll() ?: [];
        } catch (Throwable) {
            return $this->fetchOptions('treatments');
        }
    }

    public function hospitals(): array
    {
        if (!$this->tableExists('hospitals')) {
            return [];
        }
        try {
            return self::db()->query(
                "SELECT id, name FROM hospitals
                 WHERE status = 'active' AND deleted_at IS NULL
                 ORDER BY name ASC"
            )->fetchAll() ?: [];
        } catch (Throwable) {
            return $this->fetchOptions('hospitals');
        }
    }

    public function doctors(): array
    {
        if (!$this->tableExists('doctors')) {
            return [];
        }
        try {
            return self::db()->query(
                "SELECT id, name FROM doctors
                 WHERE status = 'active' AND deleted_at IS NULL
                 ORDER BY name ASC"
            )->fetchAll() ?: [];
        } catch (Throwable) {
            return [];
        }
    }

    private function fetchOptions(string $table): array
    {
        if (!$this->tableExists($table)) {
            return [];
        }
        try {
            return self::db()->query(
                "SELECT id, name FROM `{$table}` WHERE status = 'active' ORDER BY name ASC"
            )->fetchAll() ?: [];
        } catch (Throwable) {
            return [];
        }
    }

    private function tableExists(string $table): bool
    {
        static $cache = [];
        if (isset($cache[$table])) {
            return $cache[$table];
        }
        try {
            $stmt = Database::connection()->prepare(
                'SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table LIMIT 1'
            );
            $stmt->execute(['schema' => (string) config('database.name'), 'table' => $table]);
            $cache[$table] = (bool) $stmt->fetchColumn();
        } catch (Throwable) {
            $cache[$table] = false;
        }

        return $cache[$table];
    }
}
