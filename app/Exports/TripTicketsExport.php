<?php

namespace App\Exports;

use App\Models\TripTicket;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TripTicketsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private readonly ?string $status = null,
        private readonly ?string $from = null,
        private readonly ?string $to = null,
    ) {}

    public function query(): Builder
    {
        $query = TripTicket::with(['requester', 'approver'])
            ->orderBy('date_start', 'desc');

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->from) {
            $query->where('date_start', '>=', $this->from);
        }

        if ($this->to) {
            $query->where('date_end', '<=', $this->to);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Ticket No.',
            'Date Filed',
            'Purpose',
            'Date Start',
            'Date End',
            'Departure',
            'Return',
            'Destination',
            'Status',
            'Requested By',
            'Approved By',
            'Remarks',
        ];
    }

    public function map($ticket): array
    {
        return [
            $ticket->ticket_number,
            $ticket->date_filed->format('Y-m-d'),
            $ticket->purpose,
            $ticket->date_start?->format('Y-m-d') ?? '',
            $ticket->date_end?->format('Y-m-d') ?? '',
            $ticket->time_departure ?? '',
            $ticket->time_return ?? '',
            $ticket->destination,
            ucfirst($ticket->status),
            $ticket->requester->name,
            $ticket->approver?->name ?? '',
            $ticket->remarks ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
