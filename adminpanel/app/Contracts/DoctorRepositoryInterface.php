<?php

declare(strict_types=1);

namespace App\Contracts;

interface DoctorRepositoryInterface
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

    public function syncLanguages(int $doctorId, array $languageIds): void;

    public function syncSpecialties(int $doctorId, array $specialtyIds): void;

    public function syncTreatments(int $doctorId, array $treatmentIds): void;

    public function syncHospitals(int $doctorId, array $hospitalIds): void;

    public function languageIds(int $doctorId): array;

    public function specialtyIds(int $doctorId): array;

    public function treatmentIds(int $doctorId): array;

    public function hospitalIds(int $doctorId): array;

    public function exportRows(array $filters): array;
}
