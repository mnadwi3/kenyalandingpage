<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\ImageUploader;
use App\Helpers\Validator;
use App\Models\Testimonial;
use App\Repositories\TestimonialRepository;
use App\Support\Paginator;
use RuntimeException;

final class TestimonialService
{
    private TestimonialRepository $testimonials;
    private ImageUploader $images;

    public function __construct(?TestimonialRepository $testimonials = null, ?ImageUploader $images = null)
    {
        $this->testimonials = $testimonials ?? new TestimonialRepository();
        $this->images = $images ?? new ImageUploader();
    }

    public function list(array $query): array
    {
        $page = max(1, (int) ($query['page'] ?? 1));
        $perPage = min(100, max(10, (int) ($query['per_page'] ?? 20)));
        $sort = (string) ($query['sort'] ?? 'sort_order');
        $direction = (string) ($query['direction'] ?? 'asc');
        $filters = [
            'q' => trim((string) ($query['q'] ?? '')),
            'status' => (string) ($query['status'] ?? ''),
            'trashed' => (string) ($query['trashed'] ?? 'active'),
        ];

        $total = $this->testimonials->countFiltered($filters);
        $lastPage = max(1, (int) ceil($total / $perPage) ?: 1);
        $page = min($page, $lastPage);

        return [
            'testimonials' => $this->testimonials->paginate($filters, $page, $perPage, $sort, $direction),
            'paginator' => new Paginator($total, $page, $perPage),
            'filters' => $filters,
            'sort' => $sort,
            'direction' => strtolower($direction) === 'desc' ? 'desc' : 'asc',
        ];
    }

    public function find(int $id, bool $withTrashed = false): ?array
    {
        return $this->testimonials->findById($id, $withTrashed);
    }

    public function activePublic(): array
    {
        return $this->testimonials->active();
    }

    public function create(array $input, ?array $thumbnailFile = null): int
    {
        $payload = $this->validatedPayload($input);
        if ($thumbnailFile && ($thumbnailFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $payload['thumbnail'] = $this->images->upload($thumbnailFile, 'site');
        }
        $payload['uuid'] = uuid();

        return $this->testimonials->create($payload);
    }

    public function update(int $id, array $input, ?array $thumbnailFile = null): void
    {
        $existing = $this->testimonials->findById($id, true);
        if ($existing === null) {
            throw new RuntimeException('Testimonial not found.');
        }
        $payload = $this->validatedPayload($input);
        if ($thumbnailFile && ($thumbnailFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $payload['thumbnail'] = $this->images->upload($thumbnailFile, 'site', $existing['thumbnail'] ?? null);
        }
        $this->testimonials->update($id, $payload);
    }

    public function softDelete(int $id): void
    {
        $this->testimonials->softDelete($id);
    }

    public function restore(int $id): void
    {
        $this->testimonials->restore($id);
    }

    private function validatedPayload(array $input): array
    {
        $input['status'] = ($input['status'] ?? '') !== '' ? $input['status'] : 'draft';

        $validator = new Validator($input);
        $validator
            ->required('patient_name', 'Patient name')
            ->maxLength('patient_name', 150, 'Patient name')
            ->in('status', Testimonial::STATUSES, 'Status');
        if ($validator->fails()) {
            throw new RuntimeException($validator->firstError());
        }

        $sortOrder = $input['sort_order'] ?? '';

        return [
            'patient_name' => trim((string) $input['patient_name']),
            'treatment_name' => $this->nullableString($input['treatment_name'] ?? null),
            'quote' => $this->nullableString($input['quote'] ?? null),
            'youtube_id' => $this->nullableString($input['youtube_id'] ?? null),
            'status' => (string) $input['status'],
            'sort_order' => $sortOrder === '' || $sortOrder === null ? 0 : (int) $sortOrder,
        ];
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
