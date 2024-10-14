<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'title',
        'rules',
        'spam_rating_limit',
        'is_spam_detection_enabled',
        'is_captcha_enabled',
        'captcha_question',
        'captcha_answer',
    ];
}
