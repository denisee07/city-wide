<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id', 'type', 'category', 'title', 'body', 'data', 'action_url', 'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markRead(): void
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    public static function push(int $userId, string $title, ?string $body = null, string $type = 'info', string $category = 'general', ?string $actionUrl = null, ?array $data = null): self
    {
        return static::create([
            'user_id' => $userId,
            'type' => $type,
            'category' => $category,
            'title' => $title,
            'body' => $body,
            'action_url' => $actionUrl,
            'data' => $data,
        ]);
    }

    public static function pushToRole(string $role, string $title, ?string $body = null, string $type = 'info', string $category = 'general', ?string $actionUrl = null): void
    {
        User::where('role', $role)->orWhereIn('role', User::OFFICIAL_ROLES)->get()->each(function ($u) use ($title, $body, $type, $category, $actionUrl) {
            static::push($u->id, $title, $body, $type, $category, $actionUrl);
        });
    }
}