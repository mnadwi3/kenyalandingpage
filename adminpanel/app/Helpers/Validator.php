<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Lightweight input validation for Phase 1 forms.
 */
final class Validator
{
    /** @var array<string, string> */
    private array $errors = [];

    /** @param array<string, mixed> $data */
    public function __construct(private array $data)
    {
    }

    public function required(string $field, string $label): self
    {
        $value = $this->data[$field] ?? null;

        if ($value === null || (is_string($value) && trim($value) === '')) {
            $this->errors[$field] = "{$label} is required.";
        }

        return $this;
    }

    public function email(string $field, string $label): self
    {
        $value = trim((string) ($this->data[$field] ?? ''));

        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "{$label} must be a valid email.";
        }

        return $this;
    }

    public function minLength(string $field, int $min, string $label): self
    {
        $value = (string) ($this->data[$field] ?? '');

        if ($value !== '' && mb_strlen($value) < $min) {
            $this->errors[$field] = "{$label} must be at least {$min} characters.";
        }

        return $this;
    }

    public function confirmed(string $field, string $confirmationField, string $label): self
    {
        if (($this->data[$field] ?? null) !== ($this->data[$confirmationField] ?? null)) {
            $this->errors[$confirmationField] = "{$label} confirmation does not match.";
        }

        return $this;
    }

    public function maxLength(string $field, int $max, string $label): self
    {
        $value = (string) ($this->data[$field] ?? '');
        if ($value !== '' && mb_strlen($value) > $max) {
            $this->errors[$field] = "{$label} must be at most {$max} characters.";
        }

        return $this;
    }

    /** @param list<string> $allowed */
    public function in(string $field, array $allowed, string $label): self
    {
        $value = (string) ($this->data[$field] ?? '');
        if ($value !== '' && !in_array($value, $allowed, true)) {
            $this->errors[$field] = "{$label} is invalid.";
        }

        return $this;
    }

    public function numeric(string $field, string $label): self
    {
        $value = $this->data[$field] ?? null;
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->errors[$field] = "{$label} must be a number.";
        }

        return $this;
    }

    public function url(string $field, string $label): self
    {
        $value = trim((string) ($this->data[$field] ?? ''));
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->errors[$field] = "{$label} must be a valid URL.";
        }

        return $this;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    /** @return array<string, string> */
    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): string
    {
        return $this->errors[array_key_first($this->errors)] ?? 'Validation failed.';
    }
}
