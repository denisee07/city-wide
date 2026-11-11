<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Complaint;
use App\Models\DocumentRequest;
use App\Models\QueueTicket;
use App\Support\BarangayFilter;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        /**
         * Badge counts rendered in both side navigation partials.
         */
        View::composer(['layouts.nav.resident', 'layouts.nav.admin'], function ($view) {
            $user = auth()->user();

            if (! $user) {
                return;
            }

            if ($user->isOfficial()) {
                /* BarangayFilter is what the controllers themselves use, so the
                   sidebar now counts exactly the rows the lists underneath it
                   show. This used to be re-derived by hand here, which is how
                   the same number came to appear twice with two different
                   answers.

                   The four totals also match DashboardController exactly, and
                   "waiting" now means only tickets actually in line, rather
                   than silently folding in the ones already at a window. */
                $scopeId = BarangayFilter::current();

                $byResident = fn ($query) => $scopeId === null
                    ? $query
                    : $query->whereHas(
                        'resident',
                        fn ($inner) => $inner->where('barangay_id', $scopeId)
                    );

                $byBarangay = fn ($query) => $scopeId === null
                    ? $query
                    : $query->where('barangay_id', $scopeId);

                $counts = [
                    'open_complaints' => $byBarangay(Complaint::query())
                        ->whereIn('status', ['received', 'in_progress', 'on_hold'])
                        ->count(),
                    'pending_docs' => $byResident(DocumentRequest::query())
                        ->whereIn('status', ['pending', 'under_review'])
                        ->count(),
                    'pending_appts' => $byResident(Appointment::query())
                        ->where('status', 'pending')
                        ->count(),
                    'waiting' => $byResident(QueueTicket::forDate())
                        ->where('status', 'waiting')
                        ->count(),
                ];
            } else {
                $counts = [
                    'complaints' => Complaint::mine($user->id)
                        ->whereIn('status', ['received', 'in_progress', 'on_hold'])
                        ->count(),
                    'documents' => DocumentRequest::where('user_id', $user->id)
                        ->whereNotIn('status', ['released', 'rejected'])
                        ->count(),
                    'appointments' => Appointment::where('user_id', $user->id)
                        ->where('status', 'pending')
                        ->count(),
                ];
            }

            $view->with('navCounts', $counts);
        });
    }
}
