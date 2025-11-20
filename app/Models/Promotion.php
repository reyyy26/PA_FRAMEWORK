<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'value',
        'starts_at',
        'ends_at',
        'is_active',
        'conditions',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'starts_at' => 'date',
        'ends_at' => 'date',
        'conditions' => 'array',
        'is_active' => 'boolean',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withTimestamps();
    }
}
