<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\DashboardRepositoryInterface;
use App\Repositories\DashboardRepository;

/**
 * Assembles dashboard view models. Chart placeholders are explicit when
 * enquiry analytics are unavailable; entity lists are never fabricated.
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
            'doctors'    => $this->repository->tableExists('doctors'),
            'treatments' => $this->repository->tableExists('treatments'),
            'hospitals'  => $this->repository->tableExists('hospitals'),
            'enquiries'  => $this->repository->tableExists('patient_enquiries'),
        ];

        return [
            'stats' => [
                [
                    'key'    => 'doctors',
                    'label'  => 'Total Doctors',
                    'value'  => $this->repository->countEntities('doctors'),
                    'ready'  => $modules['doctors'],
                    'hint'   => $modules['doctors'] ? 'Active records' : 'Awaiting Doctors module',
                    'accent' => 'blue',
                ],
                [
                    'key'    => 'treatments',
                    'label'  => 'Total Treatments',
                    'value'  => $this->repository->countEntities('treatments'),
                    'ready'  => $modules['treatments'],
                    'hint'   => $modules['treatments'] ? 'Active records' : 'Awaiting Treatments module',
                    'accent' => 'teal',
                ],
                [
                    'key'    => 'hospitals',
                    'label'  => 'Total Hospitals',
                    'value'  => $this->repository->countEntities('hospitals'),
                    'ready'  => $modules['hospitals'],
                    'hint'   => $modules['hospitals'] ? 'Active records' : 'Awaiting Hospitals module',
                    'accent' => 'indigo',
                ],
                [
                    'key'    => 'enquiries',
                    'label'  => 'Patient Enquiries',
                    'value'  => $this->repository->countEntities('patient_enquiries', false),
                    'ready'  => $modules['enquiries'],
                    'hint'   => $modules['enquiries'] ? 'All leads' : 'Awaiting Enquiries module',
                    'accent' => 'amber',
                ],
            ],
            'modules'           => $modules,
            'recent_enquiries'  => $this->repository->recentEnquiries(8),
            'recent_activities' => $this->buildActivities(),
            'chart'             => $this->buildChart(),
            'quick_actions'     => $this->quickActions($modules),
            'notifications'     => $this->buildNotifications($modules),
        ];
    }

    /** @return list<array<string, mixed>> */
    private function buildActivities(): array
    {
        $activities = [];

        foreach ($this->repository->recentLogins(6) as $login) {
            $activities[] = [
                'type'      => 'login',
                'title'     => ($login['name'] ?? 'User') . ' signed in',
                'meta'      => $login['email'] ?? '',
                'timestamp' => $login['last_login_at'] ?? null,
            ];
        }

        return $activities;
    }

    /** @return array<string, mixed> */
    private function buildChart(): array
    {
        $live = $this->repository->enquiryCountsByDay(7);

        if ($live !== []) {
            return [
                'is_placeholder' => false,
                'title'          => 'Enquiries (last 7 days)',
                'labels'         => array_column($live, 'label'),
                'values'         => array_column($live, 'value'),
            ];
        }

        return [
            'is_placeholder' => true,
            'title'          => 'Enquiries trend (sample)',
            'subtitle'       => 'Placeholder chart until Enquiries module is live',
            'labels'         => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'values'         => [4, 7, 3, 9, 6, 5, 8],
        ];
    }

    /**
     * @param array<string, bool> $modules
     * @return list<array<string, mixed>>
     */
    private function quickActions(array $modules): array
    {
        return [
            [
                'label'   => 'Add Doctor',
                'phase'   => 3,
                'enabled' => $modules['doctors'],
                'href'    => $modules['doctors'] ? url('/doctors/create') : '#',
            ],
            [
                'label'   => 'Add Treatment',
                'phase'   => 4,
                'enabled' => $modules['treatments'],
                'href'    => $modules['treatments'] ? url('/treatments/create') : '#',
            ],
            [
                'label'   => 'Add Hospital',
                'phase'   => 5,
                'enabled' => $modules['hospitals'],
                'href'    => $modules['hospitals'] ? url('/hospitals/create') : '#',
            ],
            [
                'label'   => 'View Enquiries',
                'phase'   => 7,
                'enabled' => $modules['enquiries'],
                'href'    => $modules['enquiries'] ? url('/enquiries') : '#',
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

        if (!$modules['doctors']) {
            $items[] = [
                'title' => 'Doctors module pending',
                'body'  => 'Counts will populate after Phase 3.',
            ];
        }

        if (!$modules['enquiries']) {
            $items[] = [
                'title' => 'No enquiry pipeline yet',
                'body'  => 'Patient leads appear after Phase 7.',
            ];
        }

        if ($items === []) {
            $items[] = [
                'title' => 'System ready',
                'body'  => 'All core content tables are available.',
            ];
        }

        return $items;
    }
}
