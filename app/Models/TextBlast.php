<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TextBlast extends Model
{
    protected $fillable = [
        'sender_id', 'scope', 'barangay_id', 'recipient_group', 'message',
        'recipient_count', 'status', 'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'recipient_count' => 'integer',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }
}