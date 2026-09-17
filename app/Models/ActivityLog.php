<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    protected $fillable = [
        0 => 'user_id',
        1 => 'occurred_at',
        2 => 'action',
        3 => 'table_name',
        4 => 'record_id',
        5 => 'details',
        6 => 'ip_address',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
        ];
    }
}
