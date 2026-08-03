<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Specialty extends Model
{
    public const STATUSES = ['draft', 'active', 'inactive', 'archived'];

    public const SORTABLE = [
        'name', 'slug', 'status', 'created_at', 'updated_at',
    ];
}
