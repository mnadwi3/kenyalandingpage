<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\View;
use App\Services\SettingsService;
use RuntimeException;

final class SettingsController extends Controller
{
    private SettingsService $settings;

    public function __construct(?SettingsService $settings = null)
    {
        $this->settings = $settings ?? new SettingsService();
    }

    public function hero(): void
    {
        Auth::requirePermission('settings.view');
        View::renderInLayout('settings/hero', 'admin', [
            'title' => 'Hero Content',
            'activeNav' => 'settings',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => url('/dashboard')],
                ['label' => 'Hero Content'],
            ],
            'user' => Auth::user(),
            'csrf' => Csrf::field(),
            'hero' => $this->settings->getHero(),
            'notifications' => [],
        ]);
    }

    public function updateHero(): void
    {
        Auth::requirePermission('settings.update');
        $this->validateCsrf();
        try {
            $this->settings->updateHero($_POST, $_FILES['image'] ?? null);
            flash('success', 'Hero content updated successfully.');
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
        }
        redirect('/settings/hero');
    }

    public function accreditations(): void
    {
        Auth::requirePermission('settings.view');
        View::renderInLayout('settings/accreditations', 'admin', [
            'title' => 'Accreditation Icons',
            'activeNav' => 'settings-accreditations',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => url('/dashboard')],
                ['label' => 'Accreditation Icons'],
            ],
            'user' => Auth::user(),
            'csrf' => Csrf::field(),
            'accreditationTypes' => $this->settings->getAccreditationTypes(),
            'notifications' => [],
        ]);
    }

    public function storeAccreditation(): void
    {
        Auth::requirePermission('settings.update');
        $this->validateCsrf();
        try {
            $this->settings->createAccreditationType($_POST, $_FILES['logo'] ?? null);
            flash('success', 'Accreditation type added successfully.');
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
        }
        redirect('/settings/accreditations');
    }

    public function updateAccreditation(string $code): void
    {
        Auth::requirePermission('settings.update');
        $this->validateCsrf();
        try {
            $this->settings->updateAccreditationType($code, $_POST, $_FILES['logo'] ?? null);
            flash('success', 'Accreditation type updated successfully.');
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
        }
        redirect('/settings/accreditations');
    }

    public function destroyAccreditation(string $code): void
    {
        Auth::requirePermission('settings.update');
        $this->validateCsrf();
        $this->settings->deleteAccreditationType($code);
        flash('success', 'Accreditation type removed.');
        redirect('/settings/accreditations');
    }
}
