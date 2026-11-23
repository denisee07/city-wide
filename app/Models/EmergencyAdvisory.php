<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyAdvisory extends Model
{
    protected $fillable = [
        'title', 'body', 'category', 'scope', 'barangay_id', 'is_critical',
        'published_by', 'published_at', 'expires_at',
    ];

    protected $casts = [
        'is_critical' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public static function categories(): array
    {
        return [
            'weather' => 'Weather warning',
            'flood' => 'Flood advisory',
            'evacuation' => 'Evacuation notice',
            'earthquake' => 'Earthquake safety',
            'fire' => 'Fire prevention',
            'safety' => 'Public safety',
            'hotline' => 'Emergency hotlines',
            'general' => 'General advisory',
        ];
    }

    public function scopeActive($q)
    {
        return $q->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            });
    }
}