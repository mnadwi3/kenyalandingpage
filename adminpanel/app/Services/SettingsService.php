<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\ImageUploader;
use App\Repositories\SiteSettingRepository;
use RuntimeException;

final class SettingsService
{
    private const DEFAULT_ACCREDITATION_TYPES = [
        ['code' => 'jci', 'label' => 'JCI', 'logo' => ''],
        ['code' => 'nabh', 'label' => 'NABH', 'logo' => ''],
        ['code' => 'nabl', 'label' => 'NABL', 'logo' => ''],
        ['code' => 'others', 'label' => 'Others', 'logo' => ''],
    ];

    /** Fixed set of quick-fact slots shown on the hospital dedicated page. */
    public const QUICK_FACT_TYPES = [
        'established' => 'Established',
        'beds' => 'Beds',
        'icu_beds' => 'ICU Beds',
        'operation_theatres' => 'Operation Theatres',
        'international_patients' => 'International Patients (Per Year)',
        'airport_distance' => 'Airport Distance',
    ];

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

    /** @return list<array{code:string,label:string,logo:string}> */
    public function getAccreditationTypes(): array
    {
        $stored = $this->settings->get('accreditation_types');

        return is_array($stored) && $stored !== [] ? array_values($stored) : self::DEFAULT_ACCREDITATION_TYPES;
    }

    /** @return array<string, array{label:string,logo:string}> */
    public function getAccreditationTypesMap(): array
    {
        $map = [];
        foreach ($this->getAccreditationTypes() as $item) {
            $map[$item['code']] = ['label' => $item['label'], 'logo' => $item['logo']];
        }

        return $map;
    }

    public function createAccreditationType(array $input, ?array $logoFile): void
    {
        $label = trim((string) ($input['label'] ?? ''));
        if ($label === '') {
            throw new RuntimeException('Label is required.');
        }
        if (!$logoFile || ($logoFile['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            throw new RuntimeException('An icon image is required.');
        }

        $list = $this->getAccreditationTypes();
        $existingCodes = array_column($list, 'code');
        $base = trim((string) preg_replace('/[^a-z0-9]+/', '_', strtolower($label)), '_');
        $base = $base !== '' ? $base : 'accreditation';
        $code = $base;
        $suffix = 2;
        while (in_array($code, $existingCodes, true)) {
            $code = $base . '_' . $suffix;
            $suffix++;
        }

        $logo = $this->images->upload($logoFile, 'accreditations');
        $list[] = ['code' => $code, 'label' => $label, 'logo' => $logo];
        $this->settings->put('accreditation_types', $list);
    }

    public function updateAccreditationType(string $code, array $input, ?array $logoFile): void
    {
        $list = $this->getAccreditationTypes();
        $found = false;
        foreach ($list as &$item) {
            if ($item['code'] !== $code) {
                continue;
            }
            $found = true;
            $label = trim((string) ($input['label'] ?? ''));
            if ($label !== '') {
                $item['label'] = $label;
            }
            if ($logoFile && ($logoFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $item['logo'] = $this->images->upload($logoFile, 'accreditations', $item['logo'] ?: null);
            }
        }
        unset($item);
        if (!$found) {
            throw new RuntimeException('Accreditation type not found.');
        }
        $this->settings->put('accreditation_types', $list);
    }

    public function deleteAccreditationType(string $code): void
    {
        $list = $this->getAccreditationTypes();
        $target = null;
        foreach ($list as $item) {
            if ($item['code'] === $code) {
                $target = $item;
                break;
            }
        }
        $list = array_values(array_filter($list, static fn (array $item): bool => $item['code'] !== $code));
        $this->settings->put('accreditation_types', $list);
        if ($target !== null && !empty($target['logo'])) {
            $this->images->delete($target['logo']);
        }
    }

    /** @return array<string, string> code => logo path (may be empty string if not uploaded) */
    public function getQuickFactIcons(): array
    {
        $stored = $this->settings->get('quick_fact_icons');
        $stored = is_array($stored) ? $stored : [];
        $icons = [];
        foreach (array_keys(self::QUICK_FACT_TYPES) as $code) {
            $icons[$code] = (string) ($stored[$code] ?? '');
        }

        return $icons;
    }

    public function updateQuickFactIcon(string $code, ?array $iconFile): void
    {
        if (!isset(self::QUICK_FACT_TYPES[$code])) {
            throw new RuntimeException('Unknown quick fact type.');
        }
        if (!$iconFile || ($iconFile['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            throw new RuntimeException('An icon image is required.');
        }
        $icons = $this->getQuickFactIcons();
        $icons[$code] = $this->images->upload($iconFile, 'quick-facts', $icons[$code] ?: null);
        $this->settings->put('quick_fact_icons', $icons);
    }
}
