<?php

namespace App\Observers;

use App\Mail\NewReservationMail;
use App\Models\TripTicket;
use App\Models\TripTicketLog;
use App\Models\User;
use App\Notifications\NewReservationNotification;
use App\Notifications\ReservationStatusChangedNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class TripTicketObserver
{
    public function created(TripTicket $ticket): void
    {
        TripTicketLog::create([
            'trip_ticket_id' => $ticket->id,
            'from_status'    => null,
            'to_status'      => 'pending',
            'changed_by'     => $ticket->requested_by,
        ]);

        $ticket->loadMissing('requester');

        $admins = User::where('role', 'admin')->get();

        $admins->each(fn (User $admin) => Mail::to($admin->email)->queue(new NewReservationMail($ticket)));

        Notification::send($admins, new NewReservationNotification($ticket));
    }

    public function updated(TripTicket $ticket): void
    {
        if (! $ticket->isDirty('status')) {
            return;
        }

        $actorId    = auth()->id() ?? $ticket->requested_by;
        $fromStatus = $ticket->getOriginal('status');
        $toStatus   = $ticket->status;

        TripTicketLog::create([
            'trip_ticket_id' => $ticket->id,
            'from_status'    => $fromStatus,
            'to_status'      => $toStatus,
            'changed_by'     => $actorId,
            'remarks'        => $ticket->remarks,
        ]);

        $actor = User::find($actorId);
        if (! $actor) {
            return;
        }

        $ticket->loadMissing('requester');

        $notification = new ReservationStatusChangedNotification(
            $ticket, $fromStatus, $toStatus, $actor, $ticket->remarks
        );

        if (in_array($toStatus, ['approved', 'disapproved', 'completed'])) {
            // Admin acted → notify requester only
            $ticket->requester?->notify($notification);

        } elseif ($toStatus === 'cancelled') {
            if ($actor->role !== 'admin') {
                // Staff cancelled own ticket → notify all admins
                Notification::send(User::where('role', 'admin')->get(), $notification);
            } else {
                // Admin cancelled → notify requester
                $ticket->requester?->notify($notification);
            }
        }
    }
}
