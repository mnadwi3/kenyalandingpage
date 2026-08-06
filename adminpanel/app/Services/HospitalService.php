<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\HospitalRepositoryInterface;
use App\Core\Database;
use App\Helpers\ImageUploader;
use App\Helpers\Slug;
use App\Helpers\Validator;
use App\Models\Hospital;
use App\Repositories\HospitalRepository;
use App\Repositories\RelatedOptionsRepository;
use App\Support\Paginator;
use PDOException;
use RuntimeException;

final class HospitalService
{
    private HospitalRepositoryInterface $hospitals;
    private RelatedOptionsRepository $options;
    private ImageUploader $images;
    private SettingsService $settings;

    public function __construct(
        ?HospitalRepositoryInterface $hospitals = null,
        ?RelatedOptionsRepository $options = null,
        ?ImageUploader $images = null,
        ?SettingsService $settings = null
    ) {
        $this->hospitals = $hospitals ?? new HospitalRepository();
        $this->options = $options ?? new RelatedOptionsRepository();
        $this->images = $images ?? new ImageUploader();
        $this->settings = $settings ?? new SettingsService();
    }

    public function list(array $query): array
    {
        $page = max(1, (int) ($query['page'] ?? 1));
        $perPage = min(100, max(10, (int) ($query['per_page'] ?? 20)));
        $sort = (string) ($query['sort'] ?? 'created_at');
        $direction = (string) ($query['direction'] ?? 'desc');
        $filters = [
            'q' => trim((string) ($query['q'] ?? '')),
            'status' => (string) ($query['status'] ?? ''),
            'is_featured' => $query['is_featured'] ?? '',
            'is_verified' => $query['is_verified'] ?? '',
            'hospital_type' => trim((string) ($query['hospital_type'] ?? '')),
            'country' => trim((string) ($query['country'] ?? '')),
            'city' => trim((string) ($query['city'] ?? '')),
            'accreditation' => (string) ($query['accreditation'] ?? ''),
            'treatment_id' => $query['treatment_id'] ?? '',
            'trashed' => (string) ($query['trashed'] ?? 'active'),
        ];

        $total = $this->hospitals->countFiltered($filters);
        $lastPage = max(1, (int) ceil($total / $perPage) ?: 1);
        $page = min($page, $lastPage);
        $paginator = new Paginator($total, $page, $perPage);

        return [
            'hospitals' => $this->hospitals->paginate($filters, $page, $perPage, $sort, $direction),
            'paginator' => $paginator,
            'filters' => $filters,
            'sort' => $sort,
            'direction' => strtolower($direction) === 'asc' ? 'asc' : 'desc',
            'options' => $this->formOptions(),
        ];
    }

    public function formOptions(): array
    {
        return [
            'treatments' => $this->options->treatments(),
            'statuses' => Hospital::STATUSES,
            'types' => Hospital::TYPES,
            'accreditations' => $this->settings->getAccreditationTypesMap(),
            'international_services' => Hospital::INTERNATIONAL_SERVICES,
        ];
    }

    public function find(int $id, bool $withTrashed = false): ?array
    {
        $hospital = $this->hospitals->findById($id, $withTrashed);

        return $hospital ? $this->hydrate($hospital) : null;
    }

    public function findBySlug(string $slug): ?array
    {
        $hospital = $this->hospitals->findBySlug($slug);
        if ($hospital === null || ($hospital['status'] ?? '') !== 'active') {
            return null;
        }

        return $this->hydrate($hospital, true);
    }

    public function create(array $input, ?array $logoFile = null, ?array $coverFile = null): int
    {
        $payload = $this->validatedPayload($input);
        $relations = $this->extractRelations($input);
        if ($logoFile && ($logoFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $payload['logo'] = $this->images->upload($logoFile, 'hospitals');
        }
        if ($coverFile && ($coverFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $payload['cover_image'] = $this->images->upload($coverFile, 'hospitals');
        }

        try {
            return Database::transaction(function () use ($payload, $relations): int {
                $id = $this->hospitals->create($payload);
                $this->syncRelations($id, $relations);

                return $id;
            });
        } catch (PDOException $e) {
            throw $this->translateDuplicateNameError($e, $payload['name']);
        }
    }

    public function update(int $id, array $input, ?array $logoFile = null, ?array $coverFile = null): void
    {
        $existing = $this->hospitals->findById($id, true);
        if ($existing === null) {
            throw new RuntimeException('Hospital not found.');
        }
        $payload = $this->validatedPayload($input, $id);
        $relations = $this->extractRelations($input);
        if ($logoFile && ($logoFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $payload['logo'] = $this->images->upload($logoFile, 'hospitals', $existing['logo'] ?? null);
        }
        if ($coverFile && ($coverFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $payload['cover_image'] = $this->images->upload($coverFile, 'hospitals', $existing['cover_image'] ?? null);
        }
        if (!empty($input['remove_logo']) && empty($payload['logo'])) {
            $this->images->delete($existing['logo'] ?? null);
            $payload['logo'] = null;
        }
        if (!empty($input['remove_cover']) && empty($payload['cover_image'])) {
            $this->images->delete($existing['cover_image'] ?? null);
            $payload['cover_image'] = null;
        }
        try {
            Database::transaction(function () use ($id, $payload, $relations): void {
                $this->hospitals->update($id, $payload);
                $this->syncRelations($id, $relations);
            });
        } catch (PDOException $e) {
            throw $this->translateDuplicateNameError($e, $payload['name']);
        }
    }

    public function softDelete(int $id): void
    {
        $this->hospitals->softDelete($id);
    }

    public function restore(int $id): void
    {
        $this->hospitals->restore($id);
    }

    private function translateDuplicateNameError(PDOException $e, string $name): RuntimeException
    {
        if ($e->getCode() === '23000' && str_contains($e->getMessage(), 'uq_hospitals_name_active')) {
            return new RuntimeException('A hospital named "' . $name . '" already exists.');
        }

        return new RuntimeException('Unable to save hospital.', 0, $e);
    }

    public function duplicate(int $id): int
    {
        $hospital = $this->find($id, true);
        if ($hospital === null) {
            throw new RuntimeException('Hospital not found.');
        }
        $slug = Slug::unique(
            (string) $hospital['slug'] . '-copy',
            fn (string $s, ?int $ignore): bool => $this->hospitals->slugExists($s, $ignore)
        );
        $data = [
            'uuid' => uuid(),
            'slug' => $slug,
            'name' => $hospital['name'] . ' (Copy)',
            'logo' => $this->images->copy((string) ($hospital['logo'] ?? ''), 'hospitals'),
            'cover_image' => $this->images->copy((string) ($hospital['cover_image'] ?? ''), 'hospitals'),
            'description' => $hospital['description'] ?? $hospital['about'] ?? null,
            'established_year' => $hospital['established_year'] ?? null,
            'number_of_beds' => $hospital['number_of_beds'] ?? null,
            'hospital_type' => $hospital['hospital_type'] ?? null,
            'address_line1' => $hospital['address_line1'] ?? null,
            'address_line2' => $hospital['address_line2'] ?? null,
            'city' => $hospital['city'] ?? null,
            'state' => $hospital['state'] ?? null,
            'country' => $hospital['country'] ?? null,
            'pincode' => $hospital['pincode'] ?? null,
            'status' => 'draft',
            'is_featured' => 0,
            'is_verified' => (int) ($hospital['is_verified'] ?? 0),
            'seo_title' => $hospital['seo_title'] ?? null,
            'seo_description' => $hospital['seo_description'] ?? null,
        ];
        return Database::transaction(function () use ($data, $hospital): int {
            $newId = $this->hospitals->create($data);
            $this->syncRelations($newId, [
                'accreditations' => $hospital['accreditation_codes'] ?? [],
                'international_services' => $hospital['international_service_codes'] ?? [],
                'treatments' => $hospital['treatment_ids'] ?? [],
            ]);

            return $newId;
        });
    }

    public function bulkSoftDelete(array $ids): int
    {
        return $this->hospitals->bulkSoftDelete($ids);
    }

    public function bulkRestore(array $ids): int
    {
        return $this->hospitals->bulkRestore($ids);
    }

    public function bulkStatus(array $ids, string $status): int
    {
        return $this->hospitals->bulkUpdateStatus($ids, $status);
    }

    public function exportRows(array $filters): array
    {
        $rows = $this->hospitals->exportRows($filters);
        $ids = array_map(static fn (array $row): int => (int) $row['id'], $rows);
        $accreditations = $this->hospitals->accreditationCodesByHospitalIds($ids);
        $services = $this->hospitals->internationalServiceCodesByHospitalIds($ids);
        foreach ($rows as &$row) {
            $id = (int) $row['id'];
            $row['accreditation_codes'] = $accreditations[$id] ?? [];
            $row['international_service_codes'] = $services[$id] ?? [];
        }
        unset($row);

        return $rows;
    }

    public function accreditationCodesByHospitalIds(array $ids): array
    {
        return $this->hospitals->accreditationCodesByHospitalIds($ids);
    }

    public function publicPath(string $slug): string
    {
        return '/hospitals/' . ltrim($slug, '/');
    }

    public function importRows(array $rows): array
    {
        $created = 0;
        $failed = 0;
        $errors = [];
        foreach ($rows as $index => $row) {
            try {
                $accreditations = $this->splitList($row['accreditations'] ?? $row['accreditation'] ?? '');
                $services = $this->splitList($row['international_services'] ?? $row['services'] ?? '');
                $input = [
                    'name' => $row['name'] ?? $row['hospital_name'] ?? '',
                    'slug' => $row['slug'] ?? '',
                    'about' => $row['about'] ?? $row['description'] ?? '',
                    'established_year' => $row['established_year'] ?? '',
                    'number_of_beds' => $row['number_of_beds'] ?? $row['beds'] ?? '',
                    'hospital_type' => $row['hospital_type'] ?? $row['type'] ?? '',
                    'address_line1' => $row['address_line1'] ?? $row['address'] ?? '',
                    'address_line2' => $row['address_line2'] ?? '',
                    'city' => $row['city'] ?? '',
                    'state' => $row['state'] ?? '',
                    'country' => $row['country'] ?? '',
                    'pincode' => $row['pincode'] ?? $row['zip'] ?? '',
                    'status' => strtolower($row['status'] ?? 'draft'),
                    'is_featured' => $row['is_featured'] ?? '0',
                    'is_verified' => $row['is_verified'] ?? '0',
                    'seo_title' => $row['seo_title'] ?? '',
                    'seo_description' => $row['seo_description'] ?? '',
                    'accreditation_codes' => $accreditations,
                    'international_service_codes' => $services,
                ];
                if (!in_array($input['status'], Hospital::STATUSES, true)) {
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

    private function hydrate(array $hospital, bool $forPublic = false): array
    {
        $id = (int) $hospital['id'];
        $hospital['about'] = $hospital['description'] ?? null;
        $hospital['accreditation_codes'] = $this->hospitals->accreditationCodes($id);
        $hospital['international_service_codes'] = $this->hospitals->internationalServiceCodes($id);
        $hospital['treatment_ids'] = $this->hospitals->treatmentIds($id);
        $hospital['accreditations'] = $this->mapAccreditations($hospital['accreditation_codes']);
        $hospital['international_services'] = $this->mapServices($hospital['international_service_codes']);
        $hospital['related_specialties'] = $this->hospitals->relatedSpecialties($id);
        $hospital['related_treatments'] = $this->hospitals->relatedTreatments($id);
        $hospital['featured_doctors'] = $this->hospitals->featuredDoctors($id, 6);
        $hospital['public_url'] = $this->publicPath((string) $hospital['slug']);
        $hospital['location'] = $this->formatLocation($hospital);
        $hospital['short_description'] = $this->shortDescription((string) ($hospital['about'] ?? ''));
        $hospital['homepage_card'] = $this->homepageCard($hospital);
        if ($forPublic) {
            $hospital['page'] = $this->dedicatedPage($hospital);
        }

        return $hospital;
    }

    private function homepageCard(array $hospital): array
    {
        return [
            'logo' => $hospital['logo'] ?? null,
            'cover_image' => $hospital['cover_image'] ?? null,
            'name' => $hospital['name'] ?? '',
            'verification_badge' => !empty($hospital['is_verified']),
            'accreditation_logos' => array_map(
                static fn (array $item): array => [
                    'code' => $item['code'],
                    'label' => $item['label'],
                    'logo' => $item['logo'],
                ],
                $hospital['accreditations'] ?? []
            ),
            'established_year' => $hospital['established_year'] ?? null,
            'number_of_beds' => $hospital['number_of_beds'] ?? null,
            'hospital_type' => $hospital['hospital_type'] ?? null,
            'address_line1' => $hospital['address_line1'] ?? null,
            'address_line2' => $hospital['address_line2'] ?? null,
            'city' => $hospital['city'] ?? null,
            'country' => $hospital['country'] ?? null,
            'pincode' => $hospital['pincode'] ?? null,
            'location' => $hospital['location'] ?? '',
            'short_description' => $hospital['short_description'] ?? '',
            'book_appointment_url' => null,
            'whatsapp_url' => null,
            'view_more_url' => $hospital['public_url'] ?? $this->publicPath((string) ($hospital['slug'] ?? '')),
        ];
    }

    private function dedicatedPage(array $hospital): array
    {
        return [
            'cover_image' => $hospital['cover_image'] ?? null,
            'about' => $hospital['about'] ?? null,
            'international_patient_services' => $hospital['international_services'] ?? [],
            'related_treatments' => $hospital['related_treatments'] ?? [],
            'featured_doctors' => $hospital['featured_doctors'] ?? [],
            'related_specialties' => $hospital['related_specialties'] ?? [],
            'contact_form' => [
                'enabled' => true,
                'action' => null,
                'fields' => ['name', 'email', 'phone', 'message', 'hospital_slug'],
            ],
            'seo' => [
                'title' => $hospital['seo_title'] ?? $hospital['name'] ?? '',
                'description' => $hospital['seo_description'] ?? '',
                'slug' => $hospital['slug'] ?? '',
                'canonical' => $hospital['public_url'] ?? '',
            ],
        ];
    }

    private function validatedPayload(array $input, ?int $ignoreId = null): array
    {
        $input['status'] = ($input['status'] ?? '') !== '' ? $input['status'] : 'draft';

        $validator = new Validator($input);
        $input['about'] = $input['about'] ?? $input['description'] ?? '';
        $validator
            ->required('name', 'Hospital name')
            ->maxLength('name', 200, 'Hospital name')
            ->required('about', 'About hospital')
            ->in('status', Hospital::STATUSES, 'Status')
            ->maxLength('hospital_type', 100, 'Hospital type')
            ->maxLength('address_line1', 191, 'Address line 1')
            ->maxLength('address_line2', 191, 'Address line 2')
            ->maxLength('city', 120, 'City')
            ->maxLength('state', 120, 'State')
            ->maxLength('country', 120, 'Country')
            ->maxLength('pincode', 20, 'Pincode')
            ->maxLength('seo_title', 255, 'SEO title')
            ->maxLength('seo_description', 320, 'Meta description')
            ->maxLength('slug', 191, 'Slug');
        if ($validator->fails()) {
            throw new RuntimeException($validator->firstError());
        }

        $name = trim((string) $input['name']);
        if ($name !== '' && $this->hospitals->nameExists($name, $ignoreId)) {
            throw new RuntimeException('A hospital named "' . $name . '" already exists.');
        }
        $slugInput = trim((string) ($input['slug'] ?? ''));
        $slug = Slug::unique(
            $slugInput !== '' ? $slugInput : $name,
            fn (string $s, ?int $ignore): bool => $this->hospitals->slugExists($s, $ignore),
            $ignoreId
        );

        $year = $input['established_year'] ?? '';
        $beds = $input['number_of_beds'] ?? '';
        if ($year !== '' && $year !== null && (!ctype_digit((string) $year) || (int) $year < 1800 || (int) $year > (int) date('Y') + 1)) {
            throw new RuntimeException('Established year is invalid.');
        }
        if ($beds !== '' && $beds !== null && (!ctype_digit((string) $beds) || (int) $beds < 0)) {
            throw new RuntimeException('Number of beds is invalid.');
        }

        $payload = [
            'slug' => $slug,
            'name' => $name,
            'description' => $this->nullableString($input['about'] ?? $input['description'] ?? null),
            'established_year' => $year === '' || $year === null ? null : (int) $year,
            'number_of_beds' => $beds === '' || $beds === null ? null : (int) $beds,
            'hospital_type' => $this->nullableString($input['hospital_type'] ?? null),
            'address_line1' => $this->nullableString($input['address_line1'] ?? null),
            'address_line2' => $this->nullableString($input['address_line2'] ?? null),
            'city' => $this->nullableString($input['city'] ?? null),
            'state' => $this->nullableString($input['state'] ?? null),
            'country' => $this->nullableString($input['country'] ?? null),
            'pincode' => $this->nullableString($input['pincode'] ?? null),
            'status' => (string) $input['status'],
            'is_featured' => !empty($input['is_featured']) ? 1 : 0,
            'is_verified' => !empty($input['is_verified']) ? 1 : 0,
            'seo_title' => $this->nullableString($input['seo_title'] ?? null) ?? $name,
            'seo_description' => $this->nullableString($input['seo_description'] ?? null),
        ];
        if ($ignoreId === null) {
            $payload['uuid'] = uuid();
        }

        return $payload;
    }

    private function syncRelations(int $hospitalId, array $relations): void
    {
        $this->hospitals->syncAccreditations($hospitalId, $relations['accreditations'] ?? []);
        $this->hospitals->syncInternationalServices($hospitalId, $relations['international_services'] ?? []);
        $this->hospitals->syncTreatments($hospitalId, $relations['treatments'] ?? []);
    }

    private function extractRelations(array $input): array
    {
        return [
            'accreditations' => array_values(array_filter(array_map('strval', (array) ($input['accreditation_codes'] ?? [])))),
            'international_services' => array_values(array_filter(array_map('strval', (array) ($input['international_service_codes'] ?? [])))),
            'treatments' => $this->idList($input['treatment_ids'] ?? []),
        ];
    }

    private function mapAccreditations(array $codes): array
    {
        $types = $this->settings->getAccreditationTypesMap();
        $items = [];
        foreach ($codes as $code) {
            if (!isset($types[$code])) {
                continue;
            }
            $meta = $types[$code];
            $items[] = [
                'code' => $code,
                'label' => $meta['label'],
                'logo' => $meta['logo'],
            ];
        }

        return $items;
    }

    private function mapServices(array $codes): array
    {
        $items = [];
        foreach ($codes as $code) {
            if (!isset(Hospital::INTERNATIONAL_SERVICES[$code])) {
                continue;
            }
            $items[] = [
                'code' => $code,
                'label' => Hospital::INTERNATIONAL_SERVICES[$code],
            ];
        }

        return $items;
    }

    private function formatLocation(array $hospital): string
    {
        $parts = array_filter([
            trim((string) ($hospital['city'] ?? '')),
            trim((string) ($hospital['state'] ?? '')),
            trim((string) ($hospital['country'] ?? '')),
        ], static fn (string $part): bool => $part !== '');

        return implode(', ', $parts);
    }

    private function shortDescription(string $about): string
    {
        $about = trim(preg_replace('/\s+/', ' ', $about) ?? $about);
        if ($about === '') {
            return '';
        }

        return mb_strlen($about) > 160 ? mb_strimwidth($about, 0, 160, '…') : $about;
    }

    private function splitList(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('strval', $value)));
        }
        $value = trim((string) $value);
        if ($value === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', preg_split('/[|,]/', $value) ?: [])));
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
