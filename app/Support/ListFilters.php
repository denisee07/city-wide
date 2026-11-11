<?php

namespace App\Support;

/**
 * Query-string helper for the list pages' filter tabs and chips.
 *
 * Pressing a status tab used to send you back to page 1 with every other
 * choice wiped: the tab link was built as `['status' => $key]`, so the search
 * box you had just typed into, the priority you had picked and the barangay
 * you drilled into from the overview all silently reset. That reads as a
 * broken page to whoever is using it.
 *
 * This takes the current query, replaces one facet, and drops the page number
 * (a new filter has nothing to do with page 3 of the old one). Passing null
 * removes the facet, which is what the "All" tab means.
 *
 * It lives here rather than in a closure inside each view because three
 * templates were carrying identical copies of it.
 */
class ListFilters
{
    /**
     * @param  string  $facet  query parameter being changed, e.g. "status"
     * @param  string|null  $value  null clears it
     * @return array<string, string> safe to hand to route() as parameters
     */
    public static function with(string $facet, ?string $value, array $extra = []): array
    {
        $query = request()->query();

        if ($value === null || $value === '') {
            unset($query[$facet]);
        } else {
            $query[$facet] = $value;
        }

        // Pagination belongs to the previous set of filters, not the new one.
        unset($query['page']);

        $merged = array_merge($query, $extra);

        /* Everything must be a string: route() happily accepts null and
           produces "?facet=" which then arrives as an empty string and is
           treated as "no filter" - correct, but only by accident. */
        return array_map(strval(...), $merged);
    }
}
