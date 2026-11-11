<?php

namespace App\Http\Controllers\Concerns;

use App\Support\BarangayFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Narrows admin listings to the barangay currently on screen.
 *
 * Three shapes of data exist in this system, and each needs a different
 * predicate:
 *
 *   1. Tables that carry their own barangay_id (complaints, announcements,
 *      jobs, lost & found). Use scoped() or scopedVisible().
 *
 *   2. Resident-owned transactions (document requests, appointments, queue
 *      tickets) which deliberately do NOT repeat barangay_id — the owning
 *      resident already carries it, and duplicating it would let the two
 *      drift apart. Use scopedByResident().
 *
 *   3. The user roster itself, which is scoped by its own barangay_id.
 *      Use scoped() directly.
 *
 * There is one administrator interface now: every officer is a City
 * Administrator, and the barangay on screen is a *view* they choose rather
 * than a permission they are confined to. The console opens on their home
 * barangay and widens to all 18 with the filter. Which barangay that is gets
 * decided in exactly one place, App\Support\BarangayFilter, so a list, its
 * sidebar badge and the header pill can never disagree — they used to be
 * worked out twice, in this trait and by hand in the view composer.
 */
trait ScopesAdminQueries
{
    /**
     * The barangay in force on this request, or null when the console is
     * showing every barangay at once.
     */
    protected function effectiveBarangayId(): ?int
    {
        return BarangayFilter::current();
    }

    /**
     * The barangay a new record belongs to when its form does not ask for one.
     *
     * A notice written by an officer based in Tagapo is a Tagapo notice, even
     * if they were browsing the city-wide board when they wrote it, so the
     * home barangay wins here and the on-screen filter is only the fallback.
     */
    protected function defaultBarangayId(): ?int
    {
        return BarangayFilter::home() ?? BarangayFilter::current();
    }

    /**
     * The barangay an officer is confined to — always null.
     *
     * The per-record guardInScope() checks in the controllers read this, so
     * they stay in place and simply never restrict now that nobody is
     * confined. They are kept deliberately: if a barangay-level permission is
     * ever reintroduced, it is restored here in one return statement instead
     * of in nine controllers that would each have forgotten the check.
     */
    protected function scopeBarangayId(): ?int
    {
        return null;
    }

    /**
     * Human-readable name of the scope in force, for the admin header, so an
     * officer always knows which barangay's data is on screen.
     */
    protected function scopeLabel(): string
    {
        return BarangayFilter::label();
    }

    /**
     * Exact-match scoping for a table that owns a barangay_id column.
     */
    protected function scoped(Builder $query, string $column = 'barangay_id'): Builder
    {
        $id = $this->effectiveBarangayId();

        return $id === null ? $query : $query->where($column, $id);
    }

    /**
     * Scoping for published content: the barangay on screen plus anything
     * carrying a null barangay_id (city-wide), because that is precisely who
     * the city published them for.
     */
    protected function scopedVisible(Builder $query, string $column = 'barangay_id'): Builder
    {
        $id = $this->effectiveBarangayId();

        if ($id === null) {
            return $query;
        }

        return $query->where(
            fn (Builder $inner) => $inner->where($column, $id)->orWhereNull($column)
        );
    }

    /**
     * Scoping for a transaction table that reaches its barangay through the
     * resident who filed it.
     */
    protected function scopedByResident(Builder $query, string $relation = 'resident'): Builder
    {
        $id = $this->effectiveBarangayId();

        if ($id === null) {
            return $query;
        }

        return $query->whereHas(
            $relation,
            fn (Builder $inner) => $inner->where('barangay_id', $id)
        );
    }

    /**
     * Scoping for a raw query that selects from users (or a table with the
     * same column) without going through a relation.
     */
    protected function scopedUsers(Builder $query): Builder
    {
        return $this->scoped($query);
    }
}
