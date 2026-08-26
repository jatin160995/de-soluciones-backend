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
        'markup_bonus_amount',
        'status',
        'paid_at',
        'paid_by',
        'notes',
    ];

    protected $casts = [
        'period_month' => 'date',
        'delivered_sales_total' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'markup_bonus_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    /**
     * commission_amount (plan-based, in commission_currency) + markup_bonus_amount
     * (always HNL) are intentionally kept as separate columns rather than summed
     * into one, because a USD volume_bonus plan can't be combined with an HNL
     * markup bonus without misrepresenting the currency. This accessor is only
     * safe to use for display when commission_currency is HNL.
     */
    public function getTotalHnlAttribute(): ?float
    {
        if ($this->commission_currency !== 'HNL') {
            return null;
        }

        return round((float) $this->commission_amount + (float) $this->markup_bonus_amount, 2);
    }

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
