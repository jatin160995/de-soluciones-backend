<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'variant_id', 'type', 'quantity', 'reason',
        'reference_type', 'reference_id', 'created_by',
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}