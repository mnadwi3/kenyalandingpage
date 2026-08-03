<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Services\DoctorService;

final class PublicDoctorController extends Controller
{
    private DoctorService $doctors;

    public function __construct(?DoctorService $doctors = null)
    {
        $this->doctors = $doctors ?? new DoctorService();
    }

    public function show(string $slug): void
    {
        if (in_array($slug, ['create', 'import', 'export'], true)) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not Found']);
            return;
        }

        $doctor = $this->doctors->findBySlug($slug);
        if ($doctor === null) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not Found']);
            return;
        }

        View::render('public/doctors/show', [
            'title' => $doctor['seo_title'] ?: $doctor['name'],
            'doctor' => $doctor,
        ]);
    }
}
