<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentCommissionPlan extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'config',
        'is_active',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
