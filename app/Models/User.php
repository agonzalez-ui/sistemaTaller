<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Notifications\VerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /* metodo de enviar correo */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
            'joined_at' => 'date',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function isAdministrator(): bool
    {
        return $this->active && $this->role?->active && $this->role?->is_administrator;
    }

    public function hasModulePermission(string $slug, string $action = 'view'): bool
    {
        if (! $this->active || ! $this->role?->active || ! in_array($action, ['view', 'create', 'edit', 'delete'], true)) {
            return false;
        }
        if ($this->isAdministrator()) {
            return true;
        }
        if (in_array($slug, ['users', 'roles', 'admin_users'], true)) {
            return false;
        }
        $module = $this->role->modules->firstWhere('slug', $slug);

        return $module?->active && $module?->pivot->can_view && $module?->pivot->{'can_'.$action};
    }
}
