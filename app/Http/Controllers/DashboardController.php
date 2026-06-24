<?php

namespace App\Http\Controllers;

use App\Models\TripTicket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $now   = now();
        $year  = (int) $request->query('year',  $now->year);
        $month = (int) $request->query('month', $now->month);

        // Date strings — avoids Carbon method-chain stubs that Intelephense misreads
        $monthStart = sprintf('%04d-%02d-01', $year, $month);
        $monthEnd   = date('Y-m-t', (int) strtotime($monthStart));

        $curMonthStart = date('Y-m-01');
        $curMonthEnd   = date('Y-m-t');
        $today         = date('Y-m-d');
        $in7Days       = date('Y-m-d', (int) strtotime('+7 days'));

        return Inertia::render('Dashboard', [
            'stats' => [
                'pending'            => TripTicket::where('status', 'pending')->count(),
                'approvedThisMonth'  => TripTicket::where('status', 'approved')
                                                  ->whereBetween('date_start', [$curMonthStart, $curMonthEnd])
                                                  ->count(),
                'completedThisMonth' => TripTicket::where('status', 'completed')
                                                  ->whereBetween('date_start', [$curMonthStart, $curMonthEnd])
                                                  ->count(),
            ],
            'calendar' => [
                'year'        => $year,
                'month'       => $month,
                'bookedDates' => $this->bookedDatesForMonth($monthStart, $monthEnd),
            ],
            'userStats' => [
                'total'    => User::count(),
                'admins'   => User::where('role', 'admin')->count(),
                'staff'    => User::where('role', 'staff')->count(),
                'active'   => User::where('is_active', true)->count(),
                'inactive' => User::where('is_active', false)->count(),
            ],
            'upcoming' => TripTicket::with('requester')
                ->where('status', 'approved')
                ->where('date_start', '<=', $in7Days)
                ->where('date_end',   '>=', $today)
                ->orderBy('date_start')
                ->get()
                ->map(fn ($t) => [
                    'ticket_number'     => $t->ticket_number,
                    'travel_date_label' => $t->travelDateLabel(),
                    'is_multi_day'      => $t->isMultiDay(),
                    'destination'       => $t->destination,
                    'requester_name'    => $t->requester->name,
                ]),
        ]);
    }

    private function bookedDatesForMonth(string $monthStart, string $monthEnd): array
    {
        $tickets = TripTicket::with('requester')
            ->where('status', 'approved')
            ->where('date_start', '<=', $monthEnd)
            ->where('date_end',   '>=', $monthStart)
            ->get(['id', 'date_start', 'date_end', 'requested_by']);

        $dates = [];

        foreach ($tickets as $ticket) {
            $cursor = Carbon::parse($ticket->date_start);
            $end    = Carbon::parse($ticket->date_end);
            $name   = $ticket->requester->name;

            while ($cursor->lte($end)) {
                $iso = $cursor->toDateString();
                if ($iso >= $monthStart && $iso <= $monthEnd) {
                    $dates[$iso] = $name;
                }
                $cursor->addDay();
            }
        }

        return $dates;
    }
}
