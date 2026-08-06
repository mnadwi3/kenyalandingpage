<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Repositories\FaqRepository;
use App\Repositories\SiteSettingRepository;
use App\Repositories\TestimonialRepository;
use App\Services\DoctorService;
use App\Services\HospitalService;
use App\Services\TreatmentService;

/**
 * Unauthenticated read-only JSON endpoints that let the static frontend
 * stay in sync with content managed in the admin panel.
 */
final class PublicContentController extends Controller
{
    private TreatmentService $treatments;
    private HospitalService $hospitals;
    private DoctorService $doctors;
    private TestimonialRepository $testimonials;
    private FaqRepository $faqs;
    private SiteSettingRepository $settings;

    public function __construct(
        ?TreatmentService $treatments = null,
        ?HospitalService $hospitals = null,
        ?DoctorService $doctors = null,
        ?TestimonialRepository $testimonials = null,
        ?FaqRepository $faqs = null,
        ?SiteSettingRepository $settings = null
    ) {
        $this->treatments = $treatments ?? new TreatmentService();
        $this->hospitals = $hospitals ?? new HospitalService();
        $this->doctors = $doctors ?? new DoctorService();
        $this->testimonials = $testimonials ?? new TestimonialRepository();
        $this->faqs = $faqs ?? new FaqRepository();
        $this->settings = $settings ?? new SiteSettingRepository();
    }

    public function treatmentsList(): void
    {
        $rows = $this->treatments->list(['status' => 'active', 'per_page' => 100])['treatments'];
        $this->cached($this->mapList($rows, [$this, 'mapTreatment']));
    }

    public function treatmentBySlug(string $slug): void
    {
        header('Access-Control-Allow-Origin: *');
        $treatment = $this->treatments->findBySlug($slug);
        if ($treatment === null) {
            $this->json(['error' => 'Not found'], 404);
        }
        $this->cached($this->mapTreatment($treatment));
    }

    public function hospitalsList(): void
    {
        $rows = $this->hospitals->list(['status' => 'active', 'per_page' => 100])['hospitals'];
        $this->cached($this->mapList($rows, [$this, 'mapHospital']));
    }

    public function doctorsList(): void
    {
        $rows = $this->doctors->list(['status' => 'active', 'per_page' => 200])['doctors'];
        $this->cached($this->mapList($rows, [$this, 'mapDoctor']));
    }

    public function testimonialsList(): void
    {
        $rows = $this->testimonials->active();
        $this->cached($this->mapList($rows, [$this, 'mapTestimonial']));
    }

    public function faqsList(): void
    {
        $rows = $this->faqs->active();
        $this->cached($this->mapList($rows, [$this, 'mapFaq']));
    }

    public function hero(): void
    {
        $hero = $this->settings->get('hero') ?? [];
        $hero['image'] = $this->imageUrl($hero['image'] ?? null);
        $this->cached($hero);
    }

    /** @param array<int, array<string, mixed>> $rows */
    private function mapList(array $rows, callable $mapper): array
    {
        return array_map($mapper, $rows);
    }

    private function mapTreatment(array $t): array
    {
        return [
            'id' => (int) $t['id'],
            'slug' => (string) $t['slug'],
            'name' => (string) $t['name'],
            'category' => $t['category'] ?? null,
            'overview' => $t['overview'] ?? null,
            'symptoms' => $t['symptoms'] ?? null,
            'when_needed' => $t['when_needed'] ?? null,
            'procedure_overview' => $t['procedure_overview'] ?? null,
            'recovery' => $t['recovery'] ?? null,
            'why_choose' => $t['why_choose'] ?? null,
            'is_featured' => (bool) ($t['is_featured'] ?? false),
            'image' => $this->imageUrl($t['featured_image'] ?? null),
            'seo_title' => $t['seo_title'] ?? $t['name'],
            'seo_description' => $t['seo_description'] ?? null,
            'url' => '/treatments/' . $t['slug'] . '.html',
        ];
    }

    private function mapHospital(array $h): array
    {
        $location = implode(', ', array_filter([
            trim((string) ($h['city'] ?? '')),
            trim((string) ($h['state'] ?? '')),
            trim((string) ($h['country'] ?? '')),
        ], static fn (string $part): bool => $part !== ''));

        return [
            'id' => (int) $h['id'],
            'slug' => (string) $h['slug'],
            'name' => (string) $h['name'],
            'description' => $h['description'] ?? $h['about'] ?? null,
            'city' => $h['city'] ?? null,
            'state' => $h['state'] ?? null,
            'country' => $h['country'] ?? null,
            'location' => $location !== '' ? $location : null,
            'logo' => $this->imageUrl($h['logo'] ?? null),
            'cover_image' => $this->imageUrl($h['cover_image'] ?? null),
            'established_year' => $h['established_year'] ?? null,
            'number_of_beds' => $h['number_of_beds'] ?? null,
            'hospital_type' => $h['hospital_type'] ?? null,
            'is_featured' => (bool) ($h['is_featured'] ?? false),
            'is_verified' => (bool) ($h['is_verified'] ?? false),
            'seo_title' => $h['seo_title'] ?? $h['name'],
            'seo_description' => $h['seo_description'] ?? null,
        ];
    }

    private function mapDoctor(array $d): array
    {
        $expertise = $d['expertise'] ?? $d['experience_summary'] ?? '';
        $expertiseList = is_array($expertise)
            ? $expertise
            : array_values(array_filter(array_map('trim', preg_split('/[|,]/', (string) $expertise) ?: [])));

        return [
            'id' => (int) $d['id'],
            'slug' => (string) $d['slug'],
            'name' => (string) $d['name'],
            'specialty' => $d['designation'] ?? $d['specialty_name'] ?? '',
            'experience' => $d['years_of_experience'] ? $d['years_of_experience'] . ' Years' : '',
            'qualification' => $d['qualification'] ?? '',
            'expertise' => $expertiseList,
            'image' => $this->imageUrl($d['photo'] ?? null),
            'is_featured' => (bool) ($d['is_featured'] ?? false),
            'seo_title' => $d['seo_title'] ?? $d['name'],
            'seo_description' => $d['seo_description'] ?? null,
        ];
    }

    private function mapTestimonial(array $t): array
    {
        return [
            'id' => (int) $t['id'],
            'patient_name' => (string) $t['patient_name'],
            'treatment_name' => $t['treatment_name'] ?? null,
            'quote' => $t['quote'] ?? null,
            'youtube_id' => $t['youtube_id'] ?? null,
            'thumbnail' => $this->imageUrl($t['thumbnail'] ?? null),
        ];
    }

    private function mapFaq(array $f): array
    {
        return [
            'id' => (int) $f['id'],
            'question' => (string) $f['question'],
            'answer' => $f['answer'] ?? '',
        ];
    }

    private function imageUrl(?string $relativePath): ?string
    {
        if ($relativePath === null || $relativePath === '') {
            return null;
        }

        return asset($relativePath);
    }

    /** @param array<string, mixed>|array<int, mixed> $payload */
    private function cached(array $payload): never
    {
        header('Cache-Control: public, max-age=120');
        header('Access-Control-Allow-Origin: *');
        $this->json($payload);
    }
}
