<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentCommissionStatement extends Model
{
    protected $fillable = [
        'user_id',
        'period_month',
        'delivered_sales_total',
        'commission_amount',
        'commission_currency',
        'status',
        'paid_at',
        'paid_by',
        'notes',
    ];

    protected $casts = [
        'period_month' => 'date',
        'delivered_sales_total' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function markAsPaid(?User $by = null, ?string $note = null): void
    {
        $this->status = 'paid';
        $this->paid_at = now();
        $this->paid_by = $by?->id;

        if (filled($note)) {
            $this->notes = $note;
        }

        $this->save();
    }
}
