<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SparePartBrand extends Model
{
    protected $table = 'spare_part_brands';

    protected $fillable = [
        0 => 'name',
        1 => 'active',
    ];

    public function spareParts(): HasMany
    {
        return $this->hasMany(SparePart::class, 'spare_part_brand_id');
    }

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }
}
