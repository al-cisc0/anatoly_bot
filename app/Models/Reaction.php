<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'key_phrase',
        'response',
        'is_ai_based',
        'is_array',
        'chat_id',
        'is_class_trigger',
        'is_daily_updated',
        'is_strict',
    ];

    protected $casts = [
        'response' => 'array',
    ];
}
