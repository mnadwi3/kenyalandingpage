<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Validator;
use App\Models\Faq;
use App\Repositories\FaqRepository;
use App\Support\Paginator;
use RuntimeException;

final class FaqService
{
    private FaqRepository $faqs;

    public function __construct(?FaqRepository $faqs = null)
    {
        $this->faqs = $faqs ?? new FaqRepository();
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

        $total = $this->faqs->countFiltered($filters);
        $lastPage = max(1, (int) ceil($total / $perPage) ?: 1);
        $page = min($page, $lastPage);

        return [
            'faqs' => $this->faqs->paginate($filters, $page, $perPage, $sort, $direction),
            'paginator' => new Paginator($total, $page, $perPage),
            'filters' => $filters,
            'sort' => $sort,
            'direction' => strtolower($direction) === 'desc' ? 'desc' : 'asc',
        ];
    }

    public function find(int $id, bool $withTrashed = false): ?array
    {
        return $this->faqs->findById($id, $withTrashed);
    }

    public function activePublic(): array
    {
        return $this->faqs->active();
    }

    public function create(array $input): int
    {
        $payload = $this->validatedPayload($input);
        $payload['uuid'] = uuid();

        return $this->faqs->create($payload);
    }

    public function update(int $id, array $input): void
    {
        $existing = $this->faqs->findById($id, true);
        if ($existing === null) {
            throw new RuntimeException('FAQ not found.');
        }
        $this->faqs->update($id, $this->validatedPayload($input));
    }

    public function softDelete(int $id): void
    {
        $this->faqs->softDelete($id);
    }

    public function restore(int $id): void
    {
        $this->faqs->restore($id);
    }

    private function validatedPayload(array $input): array
    {
        $input['status'] = ($input['status'] ?? '') !== '' ? $input['status'] : 'draft';

        $validator = new Validator($input);
        $validator
            ->required('question', 'Question')
            ->maxLength('question', 500, 'Question')
            ->in('status', Faq::STATUSES, 'Status');
        if ($validator->fails()) {
            throw new RuntimeException($validator->firstError());
        }

        $sortOrder = $input['sort_order'] ?? '';

        return [
            'question' => trim((string) $input['question']),
            'answer' => $this->nullableString($input['answer'] ?? null),
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
