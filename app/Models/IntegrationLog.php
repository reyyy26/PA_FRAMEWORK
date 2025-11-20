<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel',
        'status',
        'payload',
        'response',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
        'processed_at' => 'datetime',
    ];
}
