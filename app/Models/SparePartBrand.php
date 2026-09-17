<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SparePartBrand extends Model
{
    protected $table = 'spare_part_brands';

    protected $fillable = [
        0 => 'name',
        1 => 'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }
}
