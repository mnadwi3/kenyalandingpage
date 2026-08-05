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
}
