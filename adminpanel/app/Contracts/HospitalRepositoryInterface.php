<?php

declare(strict_types=1);

namespace App\Contracts;

interface HospitalRepositoryInterface
{
    public function paginate(array $filters, int $page, int $perPage, string $sort, string $direction): array;

    public function countFiltered(array $filters): int;

    public function findById(int $id, bool $withTrashed = false): ?array;

    public function findBySlug(string $slug, bool $withTrashed = false): ?array;

    public function slugExists(string $slug, ?int $ignoreId = null): bool;

    public function create(array $data): int;

    public function update(int $id, array $data): void;

    public function softDelete(int $id): void;

    public function restore(int $id): void;

    public function bulkSoftDelete(array $ids): int;

    public function bulkRestore(array $ids): int;

    public function bulkUpdateStatus(array $ids, string $status): int;

    public function syncAccreditations(int $hospitalId, array $codes): void;

    public function syncInternationalServices(int $hospitalId, array $serviceCodes): void;

    public function syncTreatments(int $hospitalId, array $treatmentIds): void;

    public function accreditationCodes(int $hospitalId): array;

    public function internationalServiceCodes(int $hospitalId): array;

    /** @param list<int> $hospitalIds @return array<int, list<string>> */
    public function accreditationCodesByHospitalIds(array $hospitalIds): array;

    /** @param list<int> $hospitalIds @return array<int, list<string>> */
    public function internationalServiceCodesByHospitalIds(array $hospitalIds): array;

    public function treatmentIds(int $hospitalId): array;

    public function relatedTreatments(int $hospitalId): array;

    public function relatedSpecialties(int $hospitalId): array;

    public function featuredDoctors(int $hospitalId, int $limit = 6): array;

    public function exportRows(array $filters): array;
}
