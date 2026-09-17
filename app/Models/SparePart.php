<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SparePart extends Model
{
    protected $table = 'spare_parts';

    protected $fillable = [
        0 => 'code',
        1 => 'name',
        2 => 'description',
        3 => 'spare_part_brand_id',
        4 => 'price',
        5 => 'stock_quantity',
        6 => 'minimum_quantity',
        7 => 'active',
        8 => 'created_by',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(SparePartBrand::class, 'spare_part_brand_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'spare_part_id');
    }
}
