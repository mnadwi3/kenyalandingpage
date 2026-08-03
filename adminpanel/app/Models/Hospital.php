<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Hospital extends Model
{
    public const STATUSES = ['draft', 'active', 'inactive', 'archived'];

    public const SORTABLE = [
        'name', 'city', 'country', 'hospital_type', 'status', 'is_featured',
        'established_year', 'number_of_beds', 'created_at', 'updated_at',
    ];

    public const TYPES = [
        'Multi Specialty',
        'Super Specialty',
        'Specialty',
        'Teaching Hospital',
        'Clinic',
    ];

    /** @var array<string, array{label:string, logo:string}> */
    public const ACCREDITATIONS = [
        'jci' => [
            'label' => 'JCI',
            'logo' => '/assets/images/accreditations/jci.svg',
        ],
        'nabh' => [
            'label' => 'NABH',
            'logo' => '/assets/images/accreditations/nabh.svg',
        ],
        'nabl' => [
            'label' => 'NABL',
            'logo' => '/assets/images/accreditations/nabl.svg',
        ],
        'others' => [
            'label' => 'Others',
            'logo' => '/assets/images/accreditations/others.svg',
        ],
    ];

    /** @var array<string, string> */
    public const INTERNATIONAL_SERVICES = [
        'visa_invitation' => 'Visa Invitation Letter',
        'airport_pickup' => 'Airport Pickup',
        'hotel_accommodation' => 'Hotel Accommodation',
        'interpreter' => 'Interpreter',
        'currency_exchange' => 'Currency Exchange',
        'sim_card' => 'SIM Card Assistance',
        'patient_lounge' => 'International Patient Lounge',
    ];
}
