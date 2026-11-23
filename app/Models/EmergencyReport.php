<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyReport extends Model
{
    protected $fillable = [
        'reference_no', 'user_id', 'barangay_id', 'category', 'description', 'location',
        'latitude', 'longitude', 'photo', 'destination', 'priority', 'status',
        'response_notes', 'assigned_to', 'acknowledged_at', 'escalated_at', 'resolved_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'acknowledged_at' => 'datetime',
        'escalated_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public static function categories(): array
    {
        return [
            'flood' => 'Flooding',
            'fire' => 'Fire incident',
            'earthquake' => 'Earthquake',
            'medical' => 'Medical emergency',
            'accident' => 'Accident',
            'landslide' => 'Landslide',
            'road_obstruction' => 'Road obstruction',
            'fallen_tree' => 'Fallen tree',
            'other' => 'Other emergency',
        ];
    }

    public static function statuses(): array
    {
        return [
            'submitted' => 'Submitted',
            'received' => 'Received',
            'under_review' => 'Under Review',
            'responding' => 'Responding / Assistance Dispatched',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
        ];
    }

    public function statusLabel(): string
    {
        return static::statuses()[$this->status] ?? $this->status;
    }

    public function categoryName(): string
    {
        return static::categories()[$this->category] ?? $this->category;
    }
}