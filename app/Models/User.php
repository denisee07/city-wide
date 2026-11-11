<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'barangay_id',
        'status',
        'phone',
        'purok',
        'address',
        'birth_date',
        'avatar',
        'bio',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Roles                                                               */
    /* ------------------------------------------------------------------ */

    /**
     * The only two roles the interface offers.
     *
     * resident   — signs in to the Resident portal
     * city_admin — signs in to the City Administrator console
     */
    public const ROLES = [
        'resident' => 'Resident',
        'city_admin' => 'City Administrator',
    ];

    /**
     * Every stored value that opens the City Administrator console.
     *
     * 'admin' and 'staff' are the retired barangay-level roles. Rows that
     * still carry them keep working — they simply fold into the one
     * administrator interface — so a check asks "is this an officer" instead
     * of comparing one string. Do not assign either to a new account; use
     * 'city_admin'.
     */
    public const OFFICIAL_ROLES = ['city_admin', 'admin', 'staff'];

    /** May open the /admin console. */
    public function isOfficial(): bool
    {
        return in_array($this->role, self::OFFICIAL_ROLES, true);
    }

    /**
     * The role as it should read anywhere on screen, so the retired "Barangay
     * Staff" and "Barangay Admin" names never resurface in a table or badge.
     */
    public function getRoleLabelAttribute(): string
    {
        return $this->isOfficial()
            ? self::ROLES['city_admin']
            : (self::ROLES[$this->role] ?? ucfirst($this->role));
    }

    /* ------------------------------------------------------------------ */
    /* Barangay                                                            */
    /* ------------------------------------------------------------------ */

    /**
     * For a resident this is where they live, and it decides which
     * announcements, job posts and queue board reach them. For an officer it
     * is their home barangay: the console opens filtered to it, but that is a
     * default they can change, not a ceiling. See App\Support\BarangayFilter.
     */
    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    /** The barangay an officer's console opens on, null for the whole city. */
    public function homeBarangayId(): ?int
    {
        return $this->isOfficial() ? $this->barangay_id : null;
    }

    /* ------------------------------------------------------------------ */
    /* Relations                                                           */
    /* ------------------------------------------------------------------ */

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function documentRequests(): HasMany
    {
        return $this->hasMany(DocumentRequest::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function queueTickets(): HasMany
    {
        return $this->hasMany(QueueTicket::class);
    }

    public function freedomPosts(): HasMany
    {
        return $this->hasMany(FreedomPost::class);
    }

    public function getInitialsAttribute(): string
    {
        $parts = preg_split('/\s+/', trim($this->name));

        return strtoupper(substr($parts[0] ?? 'U', 0, 1).substr($parts[1] ?? '', 0, 1));
    }
}
