<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trip Ticket — {{ $ticket->ticket_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #000;
            background: #fff;
            padding: 18px 24px;
        }

        /* ── Header ── */
        .top-section {
            border: 1px solid #000;
            border-bottom: none;
            padding: 6px 8px 4px;
            margin-bottom: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .header-table td { border: none; vertical-align: middle; padding: 0; }
        .header-logo { width: 56px; text-align: center; }
        .header-text { text-align: center; }
        .republic  { font-size: 8.5pt; letter-spacing: 0.5px; }
        .agency    { font-size: 12.5pt; font-weight: bold; margin-top: 1px; }
        .regional  { font-size: 10pt; }
        .office    { font-size: 9.5pt; }
        .addr      { font-size: 10pt; color: #333; }

        .rule-thick { border: none; border-top: 2.5px solid #000; margin: 4px 0 2px; }
        .rule-thin  { border: none; border-top: 1px solid #000;   margin: 2px 0 6px; }

        /* ── Title ── */
        .title-bar {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 4px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        /* ── Meta Block ── */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .meta-table td {
            border: none;
            padding: 3px 6px;
            font-size: 9.5pt;
            vertical-align: middle;
        }
        .lbl { font-weight: bold; white-space: nowrap; width: 1%; }

        /* ── Checklist + Fuel ── */
        .section-outer {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .section-outer > tbody > tr > td {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: top;
        }
        .section-outer > tbody > tr:last-child > td {
            border-bottom: none;
        }
        .section-head {
            font-weight: bold;
            font-size: 8.5pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-bottom: 1px solid #999;
            padding-bottom: 3px;
            margin-bottom: 4px;
        }

        /* Checklist two-column inner table */
        .cl-table { width: 100%; border-collapse: collapse; }
        .cl-table td {
            border: none;
            padding: 1px 0;
            font-size: 8.5pt;
            vertical-align: top;
            white-space: nowrap;
            width: 50%;
        }

        /* Fuel consumption inner table */
        .fuel-table { width: 100%; border-collapse: collapse; }
        .fuel-table td {
            border: none;
            padding: 2px 0;
            font-size: 8.5pt;
            vertical-align: middle;
        }
        .fuel-label { width: 70%; }
        .fuel-val   { width: 30%; text-align: right; white-space: nowrap; }
        .fuel-bold td { font-weight: bold; }

        /* ── Trip Log Table ── */
        .trip-log {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .trip-log th, .trip-log td {
            border: 1px solid #000;
            padding: 3px 4px;
            font-size: 8pt;
            vertical-align: middle;
            text-align: left;
        }
        .trip-log th {
            background: #e0e0e0;
            font-weight: bold;
            text-align: center;
        }
        .trip-log .ctr { text-align: center; }
        .col-no    { width: 4%; }
        .col-tout  { width: 7%; }
        .col-tin   { width: 7%; }
        .col-dest  { width: 15%; }
        .col-purp  { width: 22%; }
        .col-pass  { width: 23%; }
        .col-spd   { width: 8%; }
        .col-dist  { width: 7%; }
        .data-row td { height: 36px; }
        .return-row td { color: #444; font-style: italic; }

        /* ── Signature Block ── */
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
            border: 1px solid #000;
            border-top: none;
        }
        .sig-table td {
            width: 33.33%;
            border: none;
            padding: 5px 8px;
            text-align: left;
            vertical-align: top;
        }
        .sig-role    { font-weight: bold; font-size: 9pt; }
        .sig-spacer  { height: 40px; }
        .sig-input {
            width: 88%;
            border: none;
            border-bottom: 1px solid #000;
            background: transparent;
            font-family: Arial, sans-serif;
            font-size: 10pt;
            text-align: left;
            outline: none;
            display: block;
            margin: 0 0 2px;
        }
        .sig-blank {
            display: block;
            width: 88%;
            margin: 0 0 2px;
            border-top: 1px solid #000;
            height: 1px;
        }
        .sig-sub { font-size: 7.5pt; color: #444; margin-top: 1px; }

        /* ── Footer ── */
        .footer {
            margin-top: 10px;
            font-size: 8pt;
            color: #666;
            text-align: center;
        }

        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
            @page { size: 13in 8.5in; margin: 12mm 18mm; }
            input.sig-input {
                -webkit-appearance: none;
                border: none;
                border-bottom: 1px solid #000;
            }
        }
    </style>
</head>
<body>

    {{-- ── Header + Title + Meta (joined top section) ── --}}
    <div class="top-section">
        <table class="header-table">
            <tr>
                <td class="header-text" colspan="2">
                    <div class="agency">DEPARTMENT OF SCIENCE AND TECHNOLOGY</div>
                    <div class="regional">Caraga Regional Office No. XIII</div>
                    <div class="addr">CSU Campus, Ampayon, Butuan City</div>
                </td>
            </tr>
        </table>
        <div class="title-bar">Vehicle Trip Ticket</div>

        {{-- ── Meta Block ── --}}
        <table class="meta-table">
            <tr>
                <td class="lbl">Car Model:</td>
                <td style="width:83%"><span style="border-bottom:1px solid #000;">{{ $ticket->vehicle?->name ?? '—' }}</span></td>
                <td class="lbl">Trip Ticket No.:</td>
                <td style="width:35%">{{ $ticket->ticket_number }}</td>
            </tr>
            <tr>
                <td class="lbl">Plate No.:</td>
                <td><span style="border-bottom:1px solid #000;">{{ $ticket->vehicle?->plate_number ?? '—' }}</span></td>
                <td class="lbl">Date:</td>
                <td>{{ $ticket->date_filed->format('F d, Y') }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td class="lbl">Driver:</td>
                <td id="driver-meta">&nbsp;</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td class="lbl">Date of Travel:</td>
                <td>{{ $ticket->travelDateLabel() }}</td>
            </tr>
        </table>
    </div>

    {{-- ── Driver's Checklist + Fuel Consumption ── --}}
    <table class="section-outer">
        <tr>
            {{-- Col 1: Driver's Checklist --}}
            <td style="width:34%;padding:0;">
                <table style="width:100%;border-collapse:collapse;">
                    <tr>
                        <td colspan="2" style="border-bottom:1px solid #000;padding:4px 6px;font-weight:bold;font-size:8.5pt;text-transform:uppercase;letter-spacing:0.3px;">Driver's Checklist</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding:5px 6px;">
                <table class="cl-table">
                    <tr>
                        <td>___ Oil Pressure</td>
                        <td>___ Engine Noises</td>
                    </tr>
                    <tr>
                        <td>___ Water Temperature</td>
                        <td>___ Spark Control</td>
                    </tr>
                    <tr>
                        <td>___ Windshield</td>
                        <td>___ Fuel Control</td>
                    </tr>
                    <tr>
                        <td>___ Lights &amp; Horns</td>
                        <td>___ Clutch</td>
                    </tr>
                    <tr>
                        <td>___ Speedometer</td>
                        <td>___ Gear Shift &amp; Transmission</td>
                    </tr>
                    <tr>
                        <td>___ Windows</td>
                        <td>___ Steering (power)</td>
                    </tr>
                    <tr>
                        <td>___ Starter</td>
                        <td>___ Reqrs/Sideview Mirror</td>
                    </tr>
                    <tr>
                        <td>___ Battery</td>
                        <td>___ Leak, Oil, Fuel &amp; Water</td>
                    </tr>
                    <tr>
                        <td>___ Tires Pins &amp; Wheels</td>
                        <td></td>
                    </tr>
                </table>
                        </td>
                    </tr>
                </table>
            </td>
            {{-- Col 2+3: Fuel (merged) --}}
            <td style="width:66%;padding:0;">
                <table style="width:100%;border-collapse:collapse;">
                    <tr>
                        <td colspan="2" style="border-bottom:1px solid #000;padding:4px 6px;font-weight:bold;font-size:8.5pt;text-transform:uppercase;letter-spacing:0.3px;">Estimated Distance Travelled &amp; Fuel Consumption</td>
                    </tr>
                    <tr>
                        <td style="width:60%;vertical-align:top;padding:5px 6px;">
                            <table class="fuel-table">
                                <tr>
                                    <td class="fuel-label">Balance in Tank:</td>
                                    <td class="fuel-val">____________ liters</td>
                                </tr>
                                <tr>
                                    <td class="fuel-label">Initial Gas-up / Refueling:</td>
                                    <td class="fuel-val">____________ liters</td>
                                </tr>
                                <tr>
                                    <td class="fuel-label">Add: Purchased during the trip:</td>
                                    <td class="fuel-val">____________ liters</td>
                                </tr>
                                <tr>
                                    <td class="fuel-label">TOTAL:</td>
                                    <td class="fuel-val">____________ liters</td>
                                </tr>
                                <tr>
                                    <td class="fuel-label">Less: Used during the trip:</td>
                                    <td class="fuel-val">____________ liters</td>
                                </tr>
                                <tr>
                                    <td class="fuel-label">Balance in Tank at end of trip:</td>
                                    <td class="fuel-val">____________ liters</td>
                                </tr>
                                <tr><td class="fuel-label">&nbsp;</td><td class="fuel-val"></td></tr>
                                <tr><td class="fuel-label">&nbsp;</td><td class="fuel-val"></td></tr>
                            </table>
                        </td>
                        <td style="width:40%;vertical-align:top;border-left:1px solid #000;padding:5px 6px;">
                            <table class="fuel-table">
                                <tr>
                                    <td class="fuel-label">Gear oil used:</td>
                                    <td class="fuel-val">________ liters</td>
                                </tr>
                                <tr>
                                    <td class="fuel-label">Lubricant oil used:</td>
                                    <td class="fuel-val">________ liters</td>
                                </tr>
                                <tr>
                                    <td class="fuel-label">Grease Issued:</td>
                                    <td class="fuel-val">________ liters</td>
                                </tr>
                                <tr><td class="fuel-label">&nbsp;</td><td></td></tr>
                                <tr><td class="fuel-label">&nbsp;</td><td></td></tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ── Trip Log Table ── --}}
    <table class="trip-log">
        <thead>
            <tr>
                <th class="col-no"  rowspan="2">Trip<br>No.</th>
                <th class="col-tout col-tin" colspan="2">TIME</th>
                <th class="col-dest" rowspan="2">DESTINATION</th>
                <th class="col-purp" rowspan="2">PURPOSE</th>
                <th class="col-pass" rowspan="2">AUTHORIZED PASSENGERS<br>&amp; SIGNATURE</th>
                <th class="col-spd col-spd" colspan="2">SPEEDOMETER<br>READING</th>
                <th class="col-dist" rowspan="2">DISTANCE<br>OF TRAVEL</th>
            </tr>
            <tr>
                <th class="col-tout">Out</th>
                <th class="col-tin">In</th>
                <th class="col-spd">Start</th>
                <th class="col-spd">End</th>
            </tr>
        </thead>
        <tbody>
            {{-- Row 1: outbound (pre-filled) --}}
            <tr class="data-row">
                <td class="ctr">1</td>
                <td class="ctr">
                    {{ $ticket->time_departure
                        ? \Carbon\Carbon::createFromTimeString($ticket->time_departure)->format('h:i A')
                        : '' }}
                </td>
                <td class="ctr">
                    {{ $ticket->time_return
                        ? \Carbon\Carbon::createFromTimeString($ticket->time_return)->format('h:i A')
                        : '' }}
                </td>
                <td>
                    {{ $ticket->destination }}
                    @if($ticket->isMultiDay())
                        <br><span style="font-size:8pt;color:#555;">({{ $ticket->travelDateLabel() }})</span>
                    @endif
                </td>
                <td>{{ $ticket->purpose }}</td>
                <td>
                    @forelse($ticket->passengers as $p)
                        {{ $p->name }}@if($p->designation) <em style="font-size:8pt;">({{ $p->designation }})</em>@endif
                        @if(!$loop->last)<br>@endif
                    @empty
                        <span style="color:#999;">—</span>
                    @endforelse
                </td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            {{-- 3 blank rows for additional manual entries --}}
            @for($i = 0; $i < 3; $i++)
            <tr class="data-row">
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
            </tr>
            @endfor
            {{-- Return row --}}
            <tr class="return-row data-row">
                <td></td>
                <td></td>
                <td></td>
                <td>Surigao City</td>
                <td>Back to Official Station</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    {{-- ── Signature Block ── --}}
    <table class="sig-table">
        <tr>
            <td>
                <div class="sig-role">Noted by:</div>
                <div class="sig-spacer"></div>
                @if($mode === 'screen')
                    <input type="text" class="sig-input" placeholder="Immediate Supervisor" style="font-weight:bold;text-transform:uppercase;" />
                @else
                    <span class="sig-blank"></span>
                @endif
                <div class="sig-sub">PSTD, PSTO-Surigao del Norte</div>
            </td>
            <td>
                <div class="sig-role">Approved:</div>
                <div class="sig-spacer"></div>
                @if($mode === 'screen')
                    <input type="text" class="sig-input"
                        value="IMELDA S. MEZO"
                        placeholder="Approving Authority"
                        style="font-weight:bold;"
                        readonly />
                @else
                    <span class="sig-blank"></span>
                @endif
                <div class="sig-sub">ARD, Finance and Administrative Services</div>
            </td>
            <td>
                <div class="sig-role">&nbsp;</div>
                <div class="sig-spacer"></div>
                @if($mode === 'screen')
                    <input type="text" id="driver-input" class="sig-input" placeholder="Driver's Name" style="font-weight:bold;text-transform:uppercase;" value="{{ strtoupper($ticket->driver_name ?? '') }}" />
                @else
                    <span class="sig-blank"></span>
                @endif
                <div class="sig-sub">Driver</div>
            </td>
        </tr>
    </table>

    @if($mode === 'screen')
    <div class="no-print" style="margin-top:20px; text-align:center;">
        <button onclick="window.print()"
                style="padding:8px 28px; font-size:11pt; cursor:pointer; background:#1d4ed8; color:#fff; border:none; border-radius:4px;">
            Print
        </button>
    </div>

    <div class="footer no-print">
        Generated by SDN-VRS &nbsp;|&nbsp; {{ now()->format('F j, Y \a\t h:i A') }}
    </div>
    @endif

    @if($mode === 'screen')
    <script>
        (function () {
            const input = document.getElementById('driver-input');
            const meta  = document.getElementById('driver-meta');

            function formatMeta(raw) {
                const name = raw.trim().toUpperCase();
                if (!name) return ' ';
                const parts = name.split(/\s+/);
                return parts.length === 1 ? name : parts[0][0] + '. ' + parts.slice(1).join(' ');
            }

            input.addEventListener('input', function () {
                meta.textContent = formatMeta(this.value);
            });

            // populate meta from stored value on load
            if (input.value) meta.textContent = formatMeta(input.value);
        })();
    </script>
    @endif

</body>
</html>
