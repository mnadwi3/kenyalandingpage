<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Doctor extends Model
{
    public const STATUSES = ['draft', 'active', 'inactive', 'archived'];
    public const SORTABLE = [
        'name', 'status', 'is_featured', 'years_of_experience', 'created_at', 'updated_at',
    ];
}
