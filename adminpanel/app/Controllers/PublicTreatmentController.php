<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Services\TreatmentService;

final class PublicTreatmentController extends Controller
{
    private TreatmentService $treatments;

    public function __construct(?TreatmentService $treatments = null)
    {
        $this->treatments = $treatments ?? new TreatmentService();
    }

    public function show(string $slug): void
    {
        if (in_array($slug, ['create', 'import', 'export'], true)) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not Found']);
            return;
        }

        $treatment = $this->treatments->findBySlug($slug);
        if ($treatment === null) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not Found']);
            return;
        }

        View::render('public/treatments/show', [
            'title' => $treatment['seo_title'] ?: $treatment['name'],
            'treatment' => $treatment,
        ]);
    }
}
