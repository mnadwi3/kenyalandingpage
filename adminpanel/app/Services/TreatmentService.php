<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\TreatmentRepositoryInterface;
use App\Core\Database;
use App\Helpers\ImageUploader;
use App\Helpers\Slug;
use App\Helpers\Validator;
use App\Models\Treatment;
use App\Repositories\RelatedOptionsRepository;
use App\Repositories\TreatmentRepository;
use App\Support\Paginator;
use RuntimeException;

final class TreatmentService
{
    private TreatmentRepositoryInterface $treatments;
    private RelatedOptionsRepository $options;
    private ImageUploader $images;

    public function __construct(
        ?TreatmentRepositoryInterface $treatments = null,
        ?RelatedOptionsRepository $options = null,
        ?ImageUploader $images = null
    ) {
        $this->treatments = $treatments ?? new TreatmentRepository();
        $this->options = $options ?? new RelatedOptionsRepository();
        $this->images = $images ?? new ImageUploader();
    }

    public function list(array $query): array
    {
        $page = max(1, (int) ($query['page'] ?? 1));
        $perPage = min(100, max(10, (int) ($query['per_page'] ?? 20)));
        $sort = (string) ($query['sort'] ?? 'created_at');
        $direction = (string) ($query['direction'] ?? 'desc');
        $filters = [
            'q'            => trim((string) ($query['q'] ?? '')),
            'status'       => (string) ($query['status'] ?? ''),
            'is_featured'  => $query['is_featured'] ?? '',
            'category'     => trim((string) ($query['category'] ?? '')),
            'specialty_id' => $query['specialty_id'] ?? '',
            'hospital_id'  => $query['hospital_id'] ?? '',
            'doctor_id'    => $query['doctor_id'] ?? '',
            'trashed'      => (string) ($query['trashed'] ?? 'active'),
        ];

        $total = $this->treatments->countFiltered($filters);
        $lastPage = max(1, (int) ceil($total / $perPage) ?: 1);
        $page = min($page, $lastPage);
        $paginator = new Paginator($total, $page, $perPage);

        return [
            'treatments' => $this->treatments->paginate($filters, $page, $perPage, $sort, $direction),
            'paginator'  => $paginator,
            'filters'    => $filters,
            'sort'       => $sort,
            'direction'  => strtolower($direction) === 'asc' ? 'asc' : 'desc',
            'options'    => $this->formOptions(),
        ];
    }

    public function formOptions(): array
    {
        return [
            'doctors'     => $this->options->doctors(),
            'hospitals'   => $this->options->hospitals(),
            'specialties' => $this->options->specialties(),
            'categories'  => $this->treatments->categories(),
            'statuses'    => Treatment::STATUSES,
        ];
    }

    public function find(int $id, bool $withTrashed = false): ?array
    {
        $treatment = $this->treatments->findById($id, $withTrashed);

        return $treatment ? $this->hydrate($treatment) : null;
    }

    public function findBySlug(string $slug): ?array
    {
        $treatment = $this->treatments->findBySlug($slug);
        if ($treatment === null || ($treatment['status'] ?? '') !== 'active') {
            return null;
        }

        return $this->hydrate($treatment);
    }

    public function create(array $input, ?array $imageFile = null): int
    {
        $payload = $this->validatedPayload($input);
        $relations = $this->extractRelations($input);
        if ($imageFile && ($imageFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $payload['featured_image'] = $this->images->upload($imageFile, 'treatments');
        }

        return Database::transaction(function () use ($payload, $relations): int {
            $id = $this->treatments->create($payload);
            $this->syncRelations($id, $relations);

            return $id;
        });
    }

    public function update(int $id, array $input, ?array $imageFile = null): void
    {
        $existing = $this->treatments->findById($id, true);
        if ($existing === null) {
            throw new RuntimeException('Treatment not found.');
        }
        $payload = $this->validatedPayload($input, $id);
        $relations = $this->extractRelations($input);
        if ($imageFile && ($imageFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $payload['featured_image'] = $this->images->upload(
                $imageFile,
                'treatments',
                $existing['featured_image'] ?? null
            );
        }
        if (!empty($input['remove_image']) && empty($payload['featured_image'])) {
            $this->images->delete($existing['featured_image'] ?? null);
            $payload['featured_image'] = null;
        }
        Database::transaction(function () use ($id, $payload, $relations): void {
            $this->treatments->update($id, $payload);
            $this->syncRelations($id, $relations);
        });
    }

    public function softDelete(int $id): void
    {
        $this->treatments->softDelete($id);
    }

    public function restore(int $id): void
    {
        $this->treatments->restore($id);
    }

    public function duplicate(int $id): int
    {
        $treatment = $this->find($id, true);
        if ($treatment === null) {
            throw new RuntimeException('Treatment not found.');
        }
        $slug = Slug::unique(
            (string) $treatment['slug'] . '-copy',
            fn (string $s, ?int $ignore): bool => $this->treatments->slugExists($s, $ignore)
        );
        $data = [
            'uuid' => uuid(),
            'slug' => $slug,
            'name' => $treatment['name'] . ' (Copy)',
            'category' => $treatment['category'] ?? null,
            'specialty_id' => $treatment['specialty_id'] ?? null,
            'overview' => $treatment['overview'] ?? $treatment['introduction'] ?? null,
            'symptoms' => $treatment['symptoms'] ?? null,
            'when_needed' => $treatment['when_needed'] ?? null,
            'procedure_overview' => $treatment['procedure_overview'] ?? null,
            'recovery' => $treatment['recovery'] ?? null,
            'why_choose' => $treatment['why_choose'] ?? null,
            'featured_image' => $this->images->copy((string) ($treatment['featured_image'] ?? ''), 'treatments'),
            'status' => 'draft',
            'is_featured' => 0,
            'sort_order' => (int) ($treatment['sort_order'] ?? 0),
            'seo_title' => $treatment['seo_title'] ?? null,
            'seo_description' => $treatment['seo_description'] ?? null,
        ];
        return Database::transaction(function () use ($data, $treatment): int {
            $newId = $this->treatments->create($data);
            $this->syncRelations($newId, [
                'doctors' => $treatment['doctor_ids'] ?? [],
                'hospitals' => $treatment['hospital_ids'] ?? [],
            ]);

            return $newId;
        });
    }

    public function bulkSoftDelete(array $ids): int
    {
        return $this->treatments->bulkSoftDelete($ids);
    }

    public function bulkRestore(array $ids): int
    {
        return $this->treatments->bulkRestore($ids);
    }

    public function bulkStatus(array $ids, string $status): int
    {
        return $this->treatments->bulkUpdateStatus($ids, $status);
    }

    public function exportRows(array $filters): array
    {
        return $this->treatments->exportRows($filters);
    }

    public function publicPath(string $slug): string
    {
        return '/treatments/' . ltrim($slug, '/');
    }

    public function importRows(array $rows): array
    {
        $created = 0;
        $failed = 0;
        $errors = [];
        foreach ($rows as $index => $row) {
            try {
                $input = [
                    'name' => $row['name'] ?? $row['treatment_name'] ?? '',
                    'slug' => $row['slug'] ?? '',
                    'category' => $row['category'] ?? '',
                    'specialty_id' => $row['specialty_id'] ?? '',
                    'introduction' => $row['introduction'] ?? $row['overview'] ?? '',
                    'symptoms' => $row['symptoms'] ?? '',
                    'when_needed' => $row['when_needed'] ?? $row['when_is_this_treatment_needed'] ?? '',
                    'procedure_overview' => $row['procedure_overview'] ?? '',
                    'recovery' => $row['recovery'] ?? $row['recovery_hospital_stay'] ?? '',
                    'why_choose' => $row['why_choose'] ?? $row['why_choose_vaidtrack'] ?? '',
                    'status' => strtolower($row['status'] ?? 'draft'),
                    'is_featured' => $row['is_featured'] ?? '0',
                    'sort_order' => $row['sort_order'] ?? '0',
                    'seo_title' => $row['seo_title'] ?? '',
                    'seo_description' => $row['seo_description'] ?? '',
                ];
                if (!in_array($input['status'], Treatment::STATUSES, true)) {
                    $input['status'] = 'draft';
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

    private function hydrate(array $treatment): array
    {
        $id = (int) $treatment['id'];
        $treatment['introduction'] = $treatment['overview'] ?? null;
        $treatment['doctor_ids'] = $this->treatments->doctorIds($id);
        $treatment['hospital_ids'] = $this->treatments->hospitalIds($id);
        $treatment['public_url'] = $this->publicPath((string) $treatment['slug']);

        return $treatment;
    }

    private function validatedPayload(array $input, ?int $ignoreId = null): array
    {
        $input['status'] = ($input['status'] ?? '') !== '' ? $input['status'] : 'draft';

        $validator = new Validator($input);
        $validator
            ->required('name', 'Treatment name')
            ->maxLength('name', 200, 'Treatment name')
            ->in('status', Treatment::STATUSES, 'Status')
            ->maxLength('category', 150, 'Category')
            ->maxLength('seo_title', 255, 'SEO title')
            ->maxLength('seo_description', 320, 'Meta description')
            ->maxLength('slug', 191, 'Slug');
        if ($validator->fails()) {
            throw new RuntimeException($validator->firstError());
        }

        $name = trim((string) $input['name']);
        $slugInput = trim((string) ($input['slug'] ?? ''));
        $slug = Slug::unique(
            $slugInput !== '' ? $slugInput : $name,
            fn (string $s, ?int $ignore): bool => $this->treatments->slugExists($s, $ignore),
            $ignoreId
        );

        $specialtyId = $input['specialty_id'] ?? '';
        $sortOrder = $input['sort_order'] ?? '';

        $payload = [
            'slug' => $slug,
            'name' => $name,
            'category' => $this->nullableString($input['category'] ?? null),
            'specialty_id' => $specialtyId === '' || $specialtyId === null ? null : (int) $specialtyId,
            'overview' => $this->nullableString($input['introduction'] ?? $input['overview'] ?? null),
            'symptoms' => $this->nullableString($input['symptoms'] ?? null),
            'when_needed' => $this->nullableString($input['when_needed'] ?? null),
            'procedure_overview' => $this->nullableString($input['procedure_overview'] ?? null),
            'recovery' => $this->nullableString($input['recovery'] ?? null),
            'why_choose' => $this->nullableString($input['why_choose'] ?? null),
            'status' => (string) $input['status'],
            'is_featured' => !empty($input['is_featured']) ? 1 : 0,
            'sort_order' => $sortOrder === '' || $sortOrder === null ? 0 : (int) $sortOrder,
            'seo_title' => $this->nullableString($input['seo_title'] ?? null) ?? $name,
            'seo_description' => $this->nullableString($input['seo_description'] ?? null),
        ];
        if ($ignoreId === null) {
            $payload['uuid'] = uuid();
        }

        return $payload;
    }

    private function syncRelations(int $treatmentId, array $relations): void
    {
        $this->treatments->syncDoctors($treatmentId, $relations['doctors'] ?? []);
        $this->treatments->syncHospitals($treatmentId, $relations['hospitals'] ?? []);
    }

    private function extractRelations(array $input): array
    {
        return [
            'doctors' => $this->idList($input['doctor_ids'] ?? []),
            'hospitals' => $this->idList($input['hospital_ids'] ?? []),
        ];
    }

    private function idList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('intval', $value))));
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
