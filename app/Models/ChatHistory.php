<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role',
        'message',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUserMessage($query)
    {
        return $query->where('role', 'user');
    }

    public function scopeAssistantMessage($query)
    {
        return $query->where('role', 'assistant');
    }
}
