<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\DoctorRepositoryInterface;
use App\Helpers\ImageUploader;
use App\Helpers\Slug;
use App\Helpers\Validator;
use App\Models\Doctor;
use App\Repositories\DoctorRepository;
use App\Repositories\RelatedOptionsRepository;
use App\Support\Paginator;
use RuntimeException;

final class DoctorService
{
    private DoctorRepositoryInterface $doctors;
    private RelatedOptionsRepository $options;
    private ImageUploader $images;

    public function __construct(
        ?DoctorRepositoryInterface $doctors = null,
        ?RelatedOptionsRepository $options = null,
        ?ImageUploader $images = null
    ) {
        $this->doctors = $doctors ?? new DoctorRepository();
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
            'specialty_id' => $query['specialty_id'] ?? '',
            'hospital_id'  => $query['hospital_id'] ?? '',
            'treatment_id' => $query['treatment_id'] ?? '',
            'trashed'      => (string) ($query['trashed'] ?? 'active'),
        ];

        $total = $this->doctors->countFiltered($filters);
        $lastPage = max(1, (int) ceil($total / $perPage) ?: 1);
        $page = min($page, $lastPage);
        $paginator = new Paginator($total, $page, $perPage);

        return [
            'doctors'   => $this->doctors->paginate($filters, $page, $perPage, $sort, $direction),
            'paginator' => $paginator,
            'filters'   => $filters,
            'sort'      => $sort,
            'direction' => strtolower($direction) === 'asc' ? 'asc' : 'desc',
            'options'   => $this->formOptions(),
        ];
    }

    public function formOptions(): array
    {
        return [
            'languages'   => $this->options->languages(),
            'specialties' => $this->options->specialties(),
            'treatments'  => $this->options->treatments(),
            'hospitals'   => $this->options->hospitals(),
            'statuses'    => Doctor::STATUSES,
        ];
    }

    public function find(int $id, bool $withTrashed = false): ?array
    {
        $doctor = $this->doctors->findById($id, $withTrashed);

        return $doctor ? $this->hydrate($doctor) : null;
    }

    public function findBySlug(string $slug): ?array
    {
        $doctor = $this->doctors->findBySlug($slug);
        if ($doctor === null || ($doctor['status'] ?? '') !== 'active') {
            return null;
        }

        return $this->hydrate($doctor);
    }

    public function create(array $input, ?array $photoFile = null): int
    {
        $payload = $this->validatedPayload($input);
        $relations = $this->extractRelations($input);
        if ($photoFile && ($photoFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $payload['photo'] = $this->images->upload($photoFile, 'doctors');
        }
        $id = $this->doctors->create($payload);
        $this->syncRelations($id, $relations);

        return $id;
    }

    public function update(int $id, array $input, ?array $photoFile = null): void
    {
        $existing = $this->doctors->findById($id, true);
        if ($existing === null) {
            throw new RuntimeException('Doctor not found.');
        }
        $payload = $this->validatedPayload($input, $id);
        $relations = $this->extractRelations($input);
        if ($photoFile && ($photoFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $payload['photo'] = $this->images->upload($photoFile, 'doctors', $existing['photo'] ?? null);
        }
        if (!empty($input['remove_photo']) && empty($payload['photo'])) {
            $this->images->delete($existing['photo'] ?? null);
            $payload['photo'] = null;
        }
        $this->doctors->update($id, $payload);
        $this->syncRelations($id, $relations);
    }

    public function softDelete(int $id): void
    {
        $this->doctors->softDelete($id);
    }

    public function restore(int $id): void
    {
        $this->doctors->restore($id);
    }

    public function duplicate(int $id): int
    {
        $doctor = $this->find($id, true);
        if ($doctor === null) {
            throw new RuntimeException('Doctor not found.');
        }
        $slug = Slug::unique(
            (string) $doctor['slug'] . '-copy',
            fn (string $s, ?int $ignore): bool => $this->doctors->slugExists($s, $ignore)
        );
        $data = [
            'uuid' => uuid(),
            'slug' => $slug,
            'name' => $doctor['name'] . ' (Copy)',
            'photo' => $this->images->copy((string) ($doctor['photo'] ?? ''), 'doctors'),
            'qualification' => $doctor['qualification'],
            'expertise' => $doctor['expertise'] ?? $doctor['experience_summary'] ?? null,
            'education' => $doctor['education'],
            'registration_number' => $doctor['registration_number'],
            'status' => 'draft',
            'is_featured' => 0,
            'seo_title' => $doctor['seo_title'],
            'seo_description' => $doctor['seo_description'],
        ];
        $newId = $this->doctors->create($data);
        $this->syncRelations($newId, [
            'languages' => $doctor['language_ids'] ?? [],
            'specialties' => $doctor['specialty_ids'] ?? [],
            'treatments' => $doctor['treatment_ids'] ?? [],
            'hospitals' => $doctor['hospital_ids'] ?? [],
        ]);

        return $newId;
    }

    public function bulkSoftDelete(array $ids): int
    {
        return $this->doctors->bulkSoftDelete($ids);
    }

    public function bulkRestore(array $ids): int
    {
        return $this->doctors->bulkRestore($ids);
    }

    public function bulkStatus(array $ids, string $status): int
    {
        return $this->doctors->bulkUpdateStatus($ids, $status);
    }

    public function exportRows(array $filters): array
    {
        return $this->doctors->exportRows($filters);
    }

    public function publicPath(string $slug): string
    {
        return '/doctors/' . ltrim($slug, '/');
    }

    public function importRows(array $rows): array
    {
        $created = 0;
        $failed = 0;
        $errors = [];
        foreach ($rows as $index => $row) {
            try {
                $input = [
                    'name' => $row['name'] ?? $row['full_name'] ?? '',
                    'slug' => $row['slug'] ?? '',
                    'qualification' => $row['qualification'] ?? '',
                    'expertise' => $row['expertise'] ?? $row['experience'] ?? $row['experience_summary'] ?? '',
                    'education' => $row['education'] ?? '',
                    'registration_number' => $row['registration_number'] ?? '',
                    'status' => strtolower($row['status'] ?? 'draft'),
                    'is_featured' => $row['is_featured'] ?? '0',
                    'seo_title' => $row['seo_title'] ?? '',
                    'seo_description' => $row['seo_description'] ?? '',
                ];
                if (!in_array($input['status'], Doctor::STATUSES, true)) {
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

    private function hydrate(array $doctor): array
    {
        $id = (int) $doctor['id'];
        if (!isset($doctor['expertise']) && isset($doctor['experience_summary'])) {
            $doctor['expertise'] = $doctor['experience_summary'];
        }
        $doctor['language_ids'] = $this->doctors->languageIds($id);
        $doctor['specialty_ids'] = $this->doctors->specialtyIds($id);
        $doctor['treatment_ids'] = $this->doctors->treatmentIds($id);
        $doctor['hospital_ids'] = $this->doctors->hospitalIds($id);
        $doctor['public_url'] = $this->publicPath((string) $doctor['slug']);

        return $doctor;
    }

    private function validatedPayload(array $input, ?int $ignoreId = null): array
    {
        $input['status'] = ($input['status'] ?? '') !== '' ? $input['status'] : 'draft';

        $validator = new Validator($input);
        $validator
            ->required('name', 'Full name')
            ->maxLength('name', 150, 'Full name')
            ->in('status', Doctor::STATUSES, 'Status')
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
            fn (string $s, ?int $ignore): bool => $this->doctors->slugExists($s, $ignore),
            $ignoreId
        );

        $payload = [
            'slug' => $slug,
            'name' => $name,
            'qualification' => $this->nullableString($input['qualification'] ?? null),
            'expertise' => $this->nullableString($input['expertise'] ?? $input['experience_summary'] ?? $input['experience'] ?? null),
            'education' => $this->nullableString($input['education'] ?? null),
            'registration_number' => $this->nullableString($input['registration_number'] ?? null),
            'status' => (string) $input['status'],
            'is_featured' => !empty($input['is_featured']) ? 1 : 0,
            'seo_title' => $this->nullableString($input['seo_title'] ?? null) ?? $name,
            'seo_description' => $this->nullableString($input['seo_description'] ?? null),
        ];
        if ($ignoreId === null) {
            $payload['uuid'] = uuid();
        }

        return $payload;
    }

    private function syncRelations(int $doctorId, array $relations): void
    {
        $this->doctors->syncLanguages($doctorId, $relations['languages'] ?? []);
        $this->doctors->syncSpecialties($doctorId, $relations['specialties'] ?? []);
        $this->doctors->syncTreatments($doctorId, $relations['treatments'] ?? []);
        $this->doctors->syncHospitals($doctorId, $relations['hospitals'] ?? []);
    }

    private function extractRelations(array $input): array
    {
        return [
            'languages' => $this->idList($input['language_ids'] ?? []),
            'specialties' => $this->idList($input['specialty_ids'] ?? []),
            'treatments' => $this->idList($input['treatment_ids'] ?? []),
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
