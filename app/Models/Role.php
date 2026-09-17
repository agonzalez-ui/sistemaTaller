<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        0 => 'name',
        1 => 'description',
        2 => 'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'is_administrator' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'module_role')
            ->withPivot(['can_view', 'can_create', 'can_edit', 'can_delete'])->withTimestamps();
    }
}
