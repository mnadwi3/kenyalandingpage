<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\SpecialtyRepositoryInterface;
use App\Helpers\ImageUploader;
use App\Helpers\Slug;
use App\Helpers\Validator;
use App\Models\Specialty;
use App\Repositories\SpecialtyRepository;
use App\Support\Paginator;
use RuntimeException;

final class SpecialtyService
{
    private SpecialtyRepositoryInterface $specialties;
    private ImageUploader $images;

    public function __construct(?SpecialtyRepositoryInterface $specialties = null, ?ImageUploader $images = null)
    {
        $this->specialties = $specialties ?? new SpecialtyRepository();
        $this->images = $images ?? new ImageUploader();
    }

    public function list(array $query): array
    {
        $page = max(1, (int) ($query['page'] ?? 1));
        $perPage = min(100, max(10, (int) ($query['per_page'] ?? 20)));
        $sort = (string) ($query['sort'] ?? 'name');
        $direction = (string) ($query['direction'] ?? 'asc');
        $filters = [
            'q' => trim((string) ($query['q'] ?? '')),
            'status' => (string) ($query['status'] ?? ''),
            'trashed' => (string) ($query['trashed'] ?? 'active'),
        ];

        $total = $this->specialties->countFiltered($filters);
        $lastPage = max(1, (int) ceil($total / $perPage) ?: 1);
        $page = min($page, $lastPage);
        $paginator = new Paginator($total, $page, $perPage);

        return [
            'specialties' => $this->specialties->paginate($filters, $page, $perPage, $sort, $direction),
            'paginator' => $paginator,
            'filters' => $filters,
            'sort' => $sort,
            'direction' => strtolower($direction) === 'asc' ? 'asc' : 'desc',
            'options' => ['statuses' => Specialty::STATUSES],
        ];
    }

    public function find(int $id, bool $withTrashed = false): ?array
    {
        return $this->specialties->findById($id, $withTrashed);
    }

    public function create(array $input, ?array $iconFile = null): int
    {
        $payload = $this->validatedPayload($input);
        if ($iconFile && ($iconFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $payload['image'] = $this->images->upload($iconFile, 'specialties');
        }

        return $this->specialties->create($payload);
    }

    public function update(int $id, array $input, ?array $iconFile = null): void
    {
        $existing = $this->specialties->findById($id, true);
        if ($existing === null) {
            throw new RuntimeException('Specialty not found.');
        }
        $payload = $this->validatedPayload($input, $id);
        if ($iconFile && ($iconFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $payload['image'] = $this->images->upload($iconFile, 'specialties', $existing['image'] ?? null);
        }
        if (!empty($input['remove_icon']) && empty($payload['image'])) {
            $this->images->delete($existing['image'] ?? null);
            $payload['image'] = null;
        }
        $this->specialties->update($id, $payload);
    }

    public function softDelete(int $id): void
    {
        $this->specialties->softDelete($id);
    }

    public function restore(int $id): void
    {
        $this->specialties->restore($id);
    }

    public function bulkSoftDelete(array $ids): int
    {
        return $this->specialties->bulkSoftDelete($ids);
    }

    public function bulkRestore(array $ids): int
    {
        return $this->specialties->bulkRestore($ids);
    }

    public function bulkStatus(array $ids, string $status): int
    {
        return $this->specialties->bulkUpdateStatus($ids, $status);
    }

    public function exportRows(array $filters): array
    {
        return $this->specialties->exportRows($filters);
    }

    public function importRows(array $rows): array
    {
        $created = 0;
        $failed = 0;
        $errors = [];
        foreach ($rows as $index => $row) {
            try {
                $input = [
                    'name' => $row['name'] ?? $row['specialty_name'] ?? '',
                    'slug' => $row['slug'] ?? '',
                    'status' => strtolower($row['status'] ?? 'active'),
                ];
                if (!in_array($input['status'], Specialty::STATUSES, true)) {
                    $input['status'] = 'active';
                }
                $this->create($input);
                $created++;
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = 'Row ' . ($index + 2) . ': ' . $e->getMessage();
            }
        }

        return compact('created', 'failed', 'errors');
    }

    private function validatedPayload(array $input, ?int $ignoreId = null): array
    {
        $input['status'] = ($input['status'] ?? '') !== '' ? $input['status'] : 'active';

        $validator = new Validator($input);
        $validator
            ->required('name', 'Specialty name')
            ->maxLength('name', 150, 'Specialty name')
            ->in('status', Specialty::STATUSES, 'Status')
            ->maxLength('slug', 191, 'Slug');
        if ($validator->fails()) {
            throw new RuntimeException($validator->firstError());
        }

        $name = trim((string) $input['name']);
        if ($this->specialties->nameExists($name, $ignoreId)) {
            throw new RuntimeException('Specialty name already exists.');
        }

        $slugInput = trim((string) ($input['slug'] ?? ''));
        $slug = Slug::unique(
            $slugInput !== '' ? $slugInput : $name,
            fn (string $s, ?int $ignore): bool => $this->specialties->slugExists($s, $ignore),
            $ignoreId
        );

        $payload = [
            'slug' => $slug,
            'name' => $name,
            'status' => (string) $input['status'],
        ];
        if ($ignoreId === null) {
            $payload['uuid'] = uuid();
        }

        return $payload;
    }
}
