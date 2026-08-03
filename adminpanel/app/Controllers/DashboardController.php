<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\View;
use App\Services\DashboardService;

final class DashboardController extends Controller
{
    private DashboardService $dashboard;

    public function __construct(?DashboardService $dashboard = null)
    {
        $this->dashboard = $dashboard ?? new DashboardService();
    }

    public function index(): void
    {
        Auth::requirePermission('dashboard.view');
        $overview = $this->dashboard->overview();

        View::renderInLayout('dashboard/index', 'admin', [
            'title' => 'Dashboard',
            'activeNav' => 'dashboard',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => url('/dashboard')],
                ['label' => 'Dashboard'],
            ],
            'user' => Auth::user(),
            'csrf' => Csrf::field(),
            'stats' => $overview['stats'],
            'recent_doctors' => $overview['recent_doctors'],
            'recent_activities' => $overview['recent_activities'],
            'chart' => $overview['chart'],
            'quick_actions' => $overview['quick_actions'],
            'notifications' => $overview['notifications'],
            'modules' => $overview['modules'],
        ]);
    }

    /** JSON payload for progressive loading / refresh. */
    public function data(): void
    {
        Auth::requirePermission('dashboard.view');
        $this->json([
            'ok' => true,
            'data' => $this->dashboard->overview(),
        ]);
    }
}
