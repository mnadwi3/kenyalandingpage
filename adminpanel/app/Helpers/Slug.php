<?php

declare(strict_types=1);

namespace App\Helpers;

final class Slug
{
    public static function make(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?? '';
        $value = trim($value, '-');

        return $value !== '' ? $value : 'item';
    }

    public static function unique(string $base, callable $exists, ?int $ignoreId = null): string
    {
        $slug = self::make($base);
        $candidate = $slug;
        $i = 2;

        while ($exists($candidate, $ignoreId)) {
            $candidate = $slug . '-' . $i;
            $i++;
        }

        return $candidate;
    }
}
