<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'product_unit_id',
        'category',
        'default_cost',
        'default_price',
        'track_batch',
        'is_active',
    ];

    protected $casts = [
        'track_batch' => 'boolean',
        'is_active' => 'boolean',
        'default_cost' => 'decimal:2',
        'default_price' => 'decimal:2',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function branchSettings(): HasMany
    {
        return $this->hasMany(ProductBranchSetting::class);
    }

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class)->withTimestamps();
    }
}
