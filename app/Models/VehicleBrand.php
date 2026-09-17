<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleBrand extends Model
{
    protected $table = 'vehicle_brands';

    protected $fillable = [
        0 => 'name',
        1 => 'active',
    ];

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'vehicle_brand_id');
    }

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }
}
