<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Services\SpecialtyService;
use App\Services\TreatmentService;

final class PublicSpecialtyController extends Controller
{
    private SpecialtyService $specialties;
    private TreatmentService $treatments;

    public function __construct(?SpecialtyService $specialties = null, ?TreatmentService $treatments = null)
    {
        $this->specialties = $specialties ?? new SpecialtyService();
        $this->treatments = $treatments ?? new TreatmentService();
    }

    public function show(string $slug): void
    {
        $specialty = $this->specialties->findBySlug($slug);
        if ($specialty === null) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not Found']);
            return;
        }

        $treatments = $this->treatments->list([
            'status' => 'active',
            'specialty_id' => $specialty['id'],
            'per_page' => 100,
        ])['treatments'];

        View::render('public/specialties/show', [
            'title' => $specialty['name'] . ' Treatments | VaidTrack.com',
            'specialty' => $specialty,
            'treatments' => $treatments,
        ]);
    }
}
