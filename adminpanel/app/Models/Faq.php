<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Faq extends Model
{
    public const STATUSES = ['draft', 'active', 'inactive', 'archived'];

    public const SORTABLE = ['question', 'status', 'sort_order', 'created_at', 'updated_at'];
}
