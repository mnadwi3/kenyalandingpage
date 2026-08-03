<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * Dashboard data access. Implementations must not invent rows —
 * return zeros / empty lists when source tables are unavailable.
 */
interface DashboardRepositoryInterface
{
    public function tableExists(string $table): bool;

    /** Count rows, optionally filtered by status = active. Returns 0 if table missing. */
    public function countEntities(string $table, bool $activeOnly = true): int;

    /** @return list<array<string, mixed>> */
    public function recentEnquiries(int $limit = 8): array;

    /** @return list<array{label: string, value: int}> Last N days enquiry counts. */
    public function enquiryCountsByDay(int $days = 7): array;

    /** @return list<array<string, mixed>> Real auth events only (e.g. recent logins). */
    public function recentLogins(int $limit = 8): array;
}
