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
        'patient_enquiries',
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
            }

            return (int) Database::connection()->query($sql)->fetchColumn();
        } catch (Throwable) {
            return 0;
        }
    }

    public function recentEnquiries(int $limit = 8): array
    {
        if (!$this->tableExists('patient_enquiries')) {
            return [];
        }

        try {
            $sql = 'SELECT pe.id, pe.uuid, pe.patient_name, pe.email, pe.phone, pe.status,
                           pe.lead_source, pe.created_at,
                           t.name AS treatment_name,
                           h.name AS hospital_name,
                           c.name AS country_name,
                           u.name AS assigned_staff_name
                    FROM patient_enquiries pe
                    LEFT JOIN treatments t ON t.id = pe.preferred_treatment_id
                    LEFT JOIN hospitals h ON h.id = pe.preferred_hospital_id
                    LEFT JOIN countries c ON c.id = pe.country_id
                    LEFT JOIN users u ON u.id = pe.assigned_staff_id
                    ORDER BY pe.created_at DESC
                    LIMIT :limit';

            $stmt = Database::connection()->prepare($sql);
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll() ?: [];
        } catch (Throwable) {
            // Joins may fail if related tables are not migrated yet.
            try {
                $stmt = Database::connection()->prepare(
                    'SELECT id, uuid, patient_name, email, phone, status, lead_source, created_at
                     FROM patient_enquiries
                     ORDER BY created_at DESC
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

    public function enquiryCountsByDay(int $days = 7): array
    {
        if (!$this->tableExists('patient_enquiries')) {
            return [];
        }

        try {
            $stmt = Database::connection()->prepare(
                'SELECT DATE(created_at) AS day_label, COUNT(*) AS total
                 FROM patient_enquiries
                 WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                 GROUP BY DATE(created_at)
                 ORDER BY day_label ASC'
            );
            $stmt->bindValue('days', $days - 1, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll() ?: [];

            $map = [];
            foreach ($rows as $row) {
                $map[$row['day_label']] = (int) $row['total'];
            }

            $series = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-{$i} days"));
                $series[] = [
                    'label' => date('D', strtotime($date)),
                    'value' => $map[$date] ?? 0,
                ];
            }

            return $series;
        } catch (Throwable) {
            return [];
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
