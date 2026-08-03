<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Services\HospitalService;

final class PublicHospitalController extends Controller
{
    private HospitalService $hospitals;

    public function __construct(?HospitalService $hospitals = null)
    {
        $this->hospitals = $hospitals ?? new HospitalService();
    }

    public function show(string $slug): void
    {
        if (in_array($slug, ['create', 'import', 'export'], true)) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not Found']);
            return;
        }

        $hospital = $this->hospitals->findBySlug($slug);
        if ($hospital === null) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not Found']);
            return;
        }

        View::render('public/hospitals/show', [
            'title' => $hospital['seo_title'] ?: $hospital['name'],
            'hospital' => $hospital,
        ]);
    }
}
