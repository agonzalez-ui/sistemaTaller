<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    protected $table = 'order_statuses';

    protected $fillable = [
        0 => 'name',
        1 => 'color_class',
        2 => 'sort_order',
        3 => 'is_final',
        4 => 'allows_invoicing',
        5 => 'active',
    ];

    protected function casts(): array
    {
        return [
            'is_final' => 'boolean',
            'allows_invoicing' => 'boolean',
            'active' => 'boolean',
        ];
    }
}
