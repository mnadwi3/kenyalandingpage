<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\DashboardRepositoryInterface;
use App\Repositories\DashboardRepository;

/**
 * Assembles dashboard view models. Entity lists are never fabricated.
 */
final class DashboardService
{
    private DashboardRepositoryInterface $repository;

    public function __construct(?DashboardRepositoryInterface $repository = null)
    {
        $this->repository = $repository ?? new DashboardRepository();
    }

    /** @return array<string, mixed> */
    public function overview(): array
    {
        $modules = [
            'doctors' => $this->repository->tableExists('doctors'),
            'treatments' => $this->repository->tableExists('treatments'),
            'hospitals' => $this->repository->tableExists('hospitals'),
            'specialties' => $this->repository->tableExists('specialties'),
        ];

        $doctorCount = $this->repository->countEntities('doctors');
        $treatmentCount = $this->repository->countEntities('treatments');
        $hospitalCount = $this->repository->countEntities('hospitals');
        $specialtyCount = $this->repository->countEntities('specialties');

        return [
            'stats' => [
                [
                    'key' => 'doctors',
                    'label' => 'Total Doctors',
                    'value' => $doctorCount,
                    'ready' => $modules['doctors'],
                    'hint' => $modules['doctors'] ? 'Active records' : 'Awaiting Doctors module',
                    'accent' => 'blue',
                ],
                [
                    'key' => 'treatments',
                    'label' => 'Total Treatments',
                    'value' => $treatmentCount,
                    'ready' => $modules['treatments'],
                    'hint' => $modules['treatments'] ? 'Active records' : 'Awaiting Treatments module',
                    'accent' => 'teal',
                ],
                [
                    'key' => 'hospitals',
                    'label' => 'Total Hospitals',
                    'value' => $hospitalCount,
                    'ready' => $modules['hospitals'],
                    'hint' => $modules['hospitals'] ? 'Active records' : 'Awaiting Hospitals module',
                    'accent' => 'indigo',
                ],
                [
                    'key' => 'specialties',
                    'label' => 'Total Specialties',
                    'value' => $specialtyCount,
                    'ready' => $modules['specialties'],
                    'hint' => $modules['specialties'] ? 'Active records' : 'Awaiting Specialties module',
                    'accent' => 'amber',
                ],
            ],
            'modules' => $modules,
            'recent_doctors' => $this->repository->recentDoctors(8),
            'recent_activities' => $this->buildActivities(),
            'chart' => [
                'is_placeholder' => false,
                'title' => 'Content overview',
                'subtitle' => 'Active records by module',
                'labels' => ['Doctors', 'Treatments', 'Hospitals', 'Specialties'],
                'values' => [$doctorCount, $treatmentCount, $hospitalCount, $specialtyCount],
            ],
            'quick_actions' => $this->quickActions($modules),
            'notifications' => $this->buildNotifications($modules),
        ];
    }

    /** @return list<array<string, mixed>> */
    private function buildActivities(): array
    {
        $activities = [];

        foreach ($this->repository->recentLogins(6) as $login) {
            $activities[] = [
                'type' => 'login',
                'title' => ($login['name'] ?? 'User') . ' signed in',
                'meta' => $login['email'] ?? '',
                'timestamp' => $login['last_login_at'] ?? null,
            ];
        }

        return $activities;
    }

    /**
     * @param array<string, bool> $modules
     * @return list<array<string, mixed>>
     */
    private function quickActions(array $modules): array
    {
        return [
            [
                'label' => 'Add Doctor',
                'phase' => 3,
                'enabled' => $modules['doctors'],
                'href' => $modules['doctors'] ? url('/doctors/create') : '#',
            ],
            [
                'label' => 'Add Treatment',
                'phase' => 4,
                'enabled' => $modules['treatments'],
                'href' => $modules['treatments'] ? url('/treatments/create') : '#',
            ],
            [
                'label' => 'Add Hospital',
                'phase' => 5,
                'enabled' => $modules['hospitals'],
                'href' => $modules['hospitals'] ? url('/hospitals/create') : '#',
            ],
            [
                'label' => 'Add Specialty',
                'phase' => 6,
                'enabled' => $modules['specialties'],
                'href' => $modules['specialties'] ? url('/specialties/create') : '#',
            ],
        ];
    }

    /**
     * @param array<string, bool> $modules
     * @return list<array<string, string>>
     */
    private function buildNotifications(array $modules): array
    {
        $items = [];

        foreach (['doctors' => 'Doctors', 'treatments' => 'Treatments', 'hospitals' => 'Hospitals', 'specialties' => 'Specialties'] as $key => $label) {
            if (!$modules[$key]) {
                $items[] = [
                    'title' => "{$label} module pending",
                    'body' => "{$label} counts will populate when the table is available.",
                ];
            }
        }

        if ($items === []) {
            $items[] = [
                'title' => 'System ready',
                'body' => 'All core content modules are available.',
            ];
        }

        return $items;
    }
}
