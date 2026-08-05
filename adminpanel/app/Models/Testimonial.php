<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Testimonial extends Model
{
    public const STATUSES = ['draft', 'active', 'inactive', 'archived'];

    public const SORTABLE = ['patient_name', 'status', 'sort_order', 'created_at', 'updated_at'];
}
