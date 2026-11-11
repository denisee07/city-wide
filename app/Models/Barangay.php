<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One of Santa Rosa City's 18 barangays.
 *
 * Carries the official name, its PSGC code and the approximate centre
 * coordinate used to position the map. Residents belong to exactly one
 * barangay; city-wide content leaves the relationship null.
 */
class Barangay extends Model
{
    protected $fillable = [
        'name',
        'alias',
        'psgc_code',
        'latitude',
        'longitude',
        'population_2020',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'double',
            'longitude' => 'double',
            'population_2020' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Scopes                                                              */
    /* ------------------------------------------------------------------ */

    /** Barangays a resident may actually be assigned to. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Alphabetical by name, the order used in every dropdown. */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('name');
    }

    /* ------------------------------------------------------------------ */
    /* Relations                                                           */
    /* ------------------------------------------------------------------ */

    public function residents(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    /* ------------------------------------------------------------------ */
    /* Presentation                                                        */
    /* ------------------------------------------------------------------ */

    /** "Kanluran (Poblacion Uno)" when an alias exists, otherwise the name. */
    public function getLabelAttribute(): string
    {
        return $this->alias ? "{$this->name} ({$this->alias})" : $this->name;
    }

    /** Short name for tight UI slots — drops the parenthetical alias. */
    public function getShortLabelAttribute(): string
    {
        return $this->name;
    }

    /** [lat, lng] pair ready to hand to Leaflet. */
    public function coordinate(): array
    {
        return [$this->latitude, $this->longitude];
    }

    /* ------------------------------------------------------------------ */
    /* City-wide defaults                                                  */
    /* ------------------------------------------------------------------ */

    /**
     * Centre of Santa Rosa City proper (the poblacion / city hall area).
     *
     * Used when no barangay is selected — e.g. the public landing map or a
     * city-wide admin view. Matches the PhilAtlas city-centre coordinate.
     */
    public const CITY_CENTER = [14.3157, 121.1122];

    /** Default zoom when showing the whole city. */
    public const CITY_ZOOM = 13;

    /** Default zoom when focused on a single barangay. */
    public const BARANGAY_ZOOM = 15;

    /** The city centre as [lat, lng]. */
    public static function cityCenter(): array
    {
        return self::CITY_CENTER;
    }

    /* ------------------------------------------------------------------ */
    /* Console-wide filter                                                 */
    /* ------------------------------------------------------------------ */

    /**
     * Which barangay the console is showing now lives in one place:
     * App\Support\BarangayFilter, read by the controllers, the sidebar badges
     * and the header pill alike.
     */
}
