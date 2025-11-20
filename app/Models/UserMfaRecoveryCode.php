<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMfaRecoveryCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'used_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    protected $hidden = [
        'code',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
