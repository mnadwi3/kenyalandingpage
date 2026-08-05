<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\ImageUploader;
use App\Repositories\SiteSettingRepository;

final class SettingsService
{
    private SiteSettingRepository $settings;
    private ImageUploader $images;

    public function __construct(?SiteSettingRepository $settings = null, ?ImageUploader $images = null)
    {
        $this->settings = $settings ?? new SiteSettingRepository();
        $this->images = $images ?? new ImageUploader();
    }

    public function getHero(): array
    {
        return $this->settings->get('hero') ?? [
            'eyebrow' => '',
            'headline' => '',
            'subheadline' => '',
            'trust_points' => [],
            'image' => '',
        ];
    }

    public function updateHero(array $input, ?array $imageFile = null): void
    {
        $current = $this->getHero();

        $trustPoints = array_values(array_filter(array_map(
            static fn (string $line): string => trim($line),
            preg_split('/\r\n|\r|\n/', (string) ($input['trust_points'] ?? '')) ?: []
        )));

        $payload = [
            'eyebrow' => trim((string) ($input['eyebrow'] ?? '')),
            'headline' => trim((string) ($input['headline'] ?? '')),
            'subheadline' => trim((string) ($input['subheadline'] ?? '')),
            'trust_points' => $trustPoints,
            'image' => $current['image'] ?? '',
        ];

        if ($imageFile && ($imageFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $payload['image'] = $this->images->upload($imageFile, 'site', $current['image'] ?: null);
        }

        $this->settings->put('hero', $payload);
    }
}
