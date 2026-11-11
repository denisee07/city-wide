<?php

namespace App\Support;

use App\Models\Barangay;
use App\Models\User;

/**
 * The barangay filter in force on the City Administrator console.
 *
 * There used to be two different answers to "which barangay is this page
 * showing": the controller trait worked it out one way and the sidebar badge
 * counter copied the logic by hand, with a comment apologising for the
 * duplication. This is now the single place that decides, so the lists, the
 * badges, the header pill and every nav link agree by construction.
 *
 * The rule:
 *
 *   no ?barangay=       the officer's home barangay, or the whole city if
 *                       their account is not tied to one
 *   ?barangay=<id>      that barangay, if it exists
 *   ?barangay=all       all 18 barangays
 *   anything else       falls back to the default, so a typo or a tampered
 *                       query string can never misdirect a query
 *
 * A home barangay is a starting point, not a permission. Every officer is a
 * City Administrator and can widen the console to the whole city.
 */
class BarangayFilter
{
    /** Query-string value that means "do not filter at all". */
    public const ALL = 'all';

    /** Query-string key used by every admin link. */
    public const KEY = 'barangay';

    /** Request attribute the resolved answer is parked on. */
    private const CACHE = 'barangay_filter';

    /**
     * The barangay every admin query should be narrowed to, or null when the
     * console is showing the whole city.
     */
    public static function current(): ?int
    {
        return static::resolve()['id'];
    }

    /** The barangay the officer's account is tied to, null for city-wide. */
    public static function home(): ?int
    {
        $user = auth()->user();

        return $user instanceof User ? $user->homeBarangayId() : null;
    }

    /**
     * Name of whatever is on screen, for the header pill and page subtitles.
     */
    public static function label(): string
    {
        return static::resolve()['label'];
    }

    /**
     * Query fragment that keeps the current choice on an admin link.
     *
     * Deliberately empty when the page is already showing the officer's own
     * barangay, so the common case keeps clean URLs.
     */
    public static function params(): array
    {
        $state = static::resolve();

        return $state['id'] === $state['home'] ? [] : [self::KEY => $state['id'] ?? self::ALL];
    }

    /** Merge the current filter into an existing query array. */
    public static function merge(array $query = []): array
    {
        return array_merge($query, static::params());
    }

    /**
     * Resolve once and hang the answer on the request object.
     *
     * Not a static property: Apache runs this app as mod_php under the
     * threaded mpm_winnt MPM, where one process serves every request, so a
     * static cache would outlive the request that filled it and show one
     * officer another officer's barangay. The request object is per-request by
     * construction, which makes the memo safe under any SAPI.
     */
    protected static function resolve(): array
    {
        $request = request();

        if ($request?->attributes->has(self::CACHE)) {
            return $request->attributes->get(self::CACHE);
        }

        $home = static::home();
        $value = $request?->query(self::KEY);

        $id = match (true) {
            $value === null || $value === '' => $home,
            $value === self::ALL => null,
            default => static::exists($value) ? (int) $value : $home,
        };

        $label = $id === null
            ? 'All 18 barangays — Santa Rosa City'
            : (Barangay::query()->whereKey($id)->value('name') ?? 'All 18 barangays — Santa Rosa City');

        $state = ['id' => $id, 'home' => $home, 'label' => $label];

        $request?->attributes->set(self::CACHE, $state);

        return $state;
    }

    /** Guards against a query string naming a barangay that does not exist. */
    protected static function exists(mixed $value): bool
    {
        if (! is_numeric($value) || (int) $value <= 0) {
            return false;
        }

        return Barangay::query()->whereKey((int) $value)->exists();
    }
}
