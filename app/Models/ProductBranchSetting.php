<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBranchSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'branch_id',
        'reorder_point',
        'reorder_qty',
        'selling_price',
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
