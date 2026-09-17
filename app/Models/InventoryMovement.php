<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    protected $table = 'inventory_movements';

    protected $fillable = [
        0 => 'spare_part_id',
        1 => 'type',
        2 => 'quantity',
        3 => 'previous_balance',
        4 => 'new_balance',
        5 => 'order_id',
        6 => 'user_id',
        7 => 'date',
        8 => 'notes',
    ];

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class, 'spare_part_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
        ];
    }
}
