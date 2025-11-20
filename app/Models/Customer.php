<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'is_opt_in',
    ];

    protected $casts = [
        'is_opt_in' => 'boolean',
    ];

    public function posSales(): HasMany
    {
        return $this->hasMany(PosSale::class);
    }
}
