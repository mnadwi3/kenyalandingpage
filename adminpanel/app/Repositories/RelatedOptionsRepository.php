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
        return $this->fetchOptions('specialties');
    }

    public function treatments(): array
    {
        return $this->fetchOptions('treatments');
    }

    public function hospitals(): array
    {
        return $this->fetchOptions('hospitals');
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
