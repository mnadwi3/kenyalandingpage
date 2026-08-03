<?php

declare(strict_types=1);

namespace App\Contracts;

interface TreatmentRepositoryInterface
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

    public function syncDoctors(int $treatmentId, array $doctorIds): void;

    public function syncHospitals(int $treatmentId, array $hospitalIds): void;

    public function doctorIds(int $treatmentId): array;

    public function hospitalIds(int $treatmentId): array;

    public function categories(): array;

    public function exportRows(array $filters): array;
}
