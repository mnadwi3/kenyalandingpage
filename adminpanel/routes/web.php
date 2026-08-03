<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\DoctorController;
use App\Controllers\HospitalController;
use App\Controllers\PublicDoctorController;
use App\Controllers\PublicHospitalController;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;

/** @var \App\Core\Router $router */

$router->get('/', static function (): void {
    if (\App\Core\Auth::check()) {
        redirect('/dashboard');
    }
    redirect('/login');
});

$router->get('/login', [AuthController::class, 'showLogin'], [GuestMiddleware::class]);
$router->post('/login', [AuthController::class, 'login'], [GuestMiddleware::class]);
$router->post('/logout', [AuthController::class, 'logout'], [AuthMiddleware::class]);
$router->get('/forgot-password', [AuthController::class, 'showForgotPassword'], [GuestMiddleware::class]);
$router->post('/forgot-password', [AuthController::class, 'sendResetLink'], [GuestMiddleware::class]);
$router->get('/reset-password', [AuthController::class, 'showResetPassword'], [GuestMiddleware::class]);
$router->post('/reset-password', [AuthController::class, 'resetPassword'], [GuestMiddleware::class]);

$router->get('/dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class]);
$router->get('/dashboard/data', [DashboardController::class, 'data'], [AuthMiddleware::class]);

$router->get('/doctors', [DoctorController::class, 'index'], [AuthMiddleware::class]);
$router->get('/doctors/create', [DoctorController::class, 'create'], [AuthMiddleware::class]);
$router->post('/doctors', [DoctorController::class, 'store'], [AuthMiddleware::class]);
$router->get('/doctors/import', [DoctorController::class, 'importForm'], [AuthMiddleware::class]);
$router->post('/doctors/import', [DoctorController::class, 'import'], [AuthMiddleware::class]);
$router->get('/doctors/export/csv', [DoctorController::class, 'exportCsv'], [AuthMiddleware::class]);
$router->get('/doctors/export/json', [DoctorController::class, 'exportJson'], [AuthMiddleware::class]);
$router->get('/doctors/export/excel', [DoctorController::class, 'exportExcel'], [AuthMiddleware::class]);
$router->post('/doctors/bulk', [DoctorController::class, 'bulk'], [AuthMiddleware::class]);
$router->get('/doctors/{id}/edit', [DoctorController::class, 'edit'], [AuthMiddleware::class]);
$router->post('/doctors/{id}', [DoctorController::class, 'update'], [AuthMiddleware::class]);
$router->post('/doctors/{id}/delete', [DoctorController::class, 'destroy'], [AuthMiddleware::class]);
$router->post('/doctors/{id}/restore', [DoctorController::class, 'restore'], [AuthMiddleware::class]);
$router->post('/doctors/{id}/duplicate', [DoctorController::class, 'duplicate'], [AuthMiddleware::class]);

$router->get('/doctors/{slug}', [PublicDoctorController::class, 'show']);

$router->get('/hospitals', [HospitalController::class, 'index'], [AuthMiddleware::class]);
$router->get('/hospitals/create', [HospitalController::class, 'create'], [AuthMiddleware::class]);
$router->post('/hospitals', [HospitalController::class, 'store'], [AuthMiddleware::class]);
$router->get('/hospitals/import', [HospitalController::class, 'importForm'], [AuthMiddleware::class]);
$router->post('/hospitals/import', [HospitalController::class, 'import'], [AuthMiddleware::class]);
$router->get('/hospitals/export/csv', [HospitalController::class, 'exportCsv'], [AuthMiddleware::class]);
$router->get('/hospitals/export/json', [HospitalController::class, 'exportJson'], [AuthMiddleware::class]);
$router->get('/hospitals/export/excel', [HospitalController::class, 'exportExcel'], [AuthMiddleware::class]);
$router->post('/hospitals/bulk', [HospitalController::class, 'bulk'], [AuthMiddleware::class]);
$router->get('/hospitals/{id}/edit', [HospitalController::class, 'edit'], [AuthMiddleware::class]);
$router->post('/hospitals/{id}', [HospitalController::class, 'update'], [AuthMiddleware::class]);
$router->post('/hospitals/{id}/delete', [HospitalController::class, 'destroy'], [AuthMiddleware::class]);
$router->post('/hospitals/{id}/restore', [HospitalController::class, 'restore'], [AuthMiddleware::class]);
$router->post('/hospitals/{id}/duplicate', [HospitalController::class, 'duplicate'], [AuthMiddleware::class]);

$router->get('/hospitals/{slug}', [PublicHospitalController::class, 'show']);
