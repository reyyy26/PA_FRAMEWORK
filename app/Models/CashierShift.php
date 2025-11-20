<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashierShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'cashier_id',
        'opening_float',
        'closing_amount',
        'opened_at',
        'closed_at',
        'closing_notes',
    ];

    protected $casts = [
        'opening_float' => 'decimal:2',
        'closing_amount' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'closing_notes' => 'array',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}
