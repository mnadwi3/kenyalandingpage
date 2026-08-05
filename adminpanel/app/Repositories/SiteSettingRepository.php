<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Model;

final class SiteSettingRepository extends Model
{
    public function get(string $key): ?array
    {
        $stmt = self::db()->prepare('SELECT setting_value FROM site_settings WHERE setting_key = :key LIMIT 1');
        $stmt->execute(['key' => $key]);
        $raw = $stmt->fetchColumn();
        if ($raw === false || $raw === null) {
            return null;
        }
        $decoded = json_decode((string) $raw, true);

        return is_array($decoded) ? $decoded : null;
    }

    public function put(string $key, array $value): void
    {
        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        self::db()->prepare(
            'INSERT INTO site_settings (setting_key, setting_value) VALUES (:key, :value)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW(3)'
        )->execute(['key' => $key, 'value' => $json]);
    }
}
