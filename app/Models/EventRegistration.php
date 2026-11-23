<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRegistration extends Model
{
    protected $fillable = [
        'event_id', 'user_id', 'reference_no', 'qr_code', 'status', 'attended_at', 'attended_by',
    ];

    protected $casts = [
        'attended_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attendant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attended_by');
    }
}