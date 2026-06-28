<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Travel Order — {{ $order->travel_order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #000;
            background: #fff;
            padding: 20px 28px;
        }

        /* ── Header ── */
        .header {
            text-align: left;
            margin-bottom: 10px;
        }

        .header-agency {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header-title {
            font-size: 8pt;
            font-weight: bold;
            margin-top: 2px;
        }

        .header-series {
            font-size: 8pt;
            margin-top: 1px;
        }

        .header-date {
            float: right;
            font-size: 9pt;
        }

        .header-title-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-top: 2px;
        }

        .preamble {
            margin-top: 14px;
            margin-bottom: 6px;
            font-size: 10pt;
        }

        /* ── Tables ── */
        .doc-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .doc-table th,
        .doc-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 9.5pt;
            vertical-align: top;
        }

        .doc-table th {
            font-weight: bold;
            text-align: center;
            background: #f0f0f0;
        }

        .doc-table td.center {
            text-align: center;
        }

        /* ── Expenses section ── */
        .section-label {
            font-size: 9.5pt;
            margin-top: 10px;
            margin-bottom: 2px;
        }

        .section-indent {
            margin-left: 20px;
        }

        .expense-main {
            font-size: 9.5pt;
            margin-top: 8px;
            margin-bottom: 3px;
        }

        .sub-table {
            width: 60%;
            border-collapse: collapse;
            margin-left: 24px;
            margin-bottom: 4px;
        }

        .sub-table td {
            border: 1px solid #000;
            padding: 3px 5px;
            font-size: 9pt;
        }

        .sub-table .check-col {
            width: 28px;
            text-align: center;
        }

        /* ── Expense Table (grouped-header grid) ── */
        .expense-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin-top: 10px;
            table-layout: fixed;
        }

        .expense-table th,
        .expense-table td {
            border: none;
            padding: 4px 6px;
            font-size: 9pt;
            vertical-align: middle;
        }

        .expense-table tbody .mark-cell u {
            display: inline-block;
            min-width: 80px;
            text-align: center;
            text-decoration: underline;
            text-decoration-thickness: 0.5px;
            text-underline-offset: 2px;
        }

        .expense-table th {
            font-weight: bold;
            text-align: center;
        }

        .expense-table .col-expense {
            width: 34%;
            text-align: left;
            vertical-align: middle;
        }

        .expense-table .col-fund {
            width: 22%;
            text-align: center;
            vertical-align: middle;
        }

        .expense-table .row-group td {
            font-weight: bold;
        }

        .expense-table .row-sub td:first-child {
            padding-left: 22px;
        }

        .expense-table .mark-cell {
            text-align: center;
            font-weight: bold;
        }

        /* ── Remarks ── */
        .remarks-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            table-layout: fixed;
            margin-top: 14px;
        }

        .remarks-table td {
            border: none;
            padding: 4px 6px;
            font-size: 9pt;
            vertical-align: middle;
        }

        .remarks-label {
            width: 34%;
            font-weight: bold;
            white-space: nowrap;
            vertical-align: middle;
            padding-bottom: 20px;
        }

        .remarks-table .remarks-line-cell {
            border-bottom: 1px solid #000;
            vertical-align: bottom;
            height: 20px;
            padding-bottom: 10px;
        }

        .remarks-boilerplate {
            font-size: 8.5pt;
            color: #333;
            font-style: italic;
            padding-top: 16px;
            text-align: justify;
        }

        /* ── Signature Block ── */
        .sig-section {
            margin-top: 28px;
        }

        .sig-table {
            width: 100%;
            border-collapse: collapse;
        }

        .sig-table td {
            width: 50%;
            padding: 6px 8px;
            vertical-align: top;
        }

        .sig-label {
            font-size: 9pt;
            font-weight: bold;
        }

        .sig-spacer {
            height: 36px;
        }

        .sig-name {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            display: block;
        }

        .sig-position {
            font-size: 8.5pt;
            margin-top: 1px;
        }

        .sig-input {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            border: none;
            text-decoration: underline;
            text-decoration-thickness: 1px;
            background: transparent;
            font-family: Arial, sans-serif;
            outline: none;
            width: 90%;
            display: block;
            margin: 0 0 2px;
        }

        .sig-position-input {
            font-size: 8.5pt;
            border: none;
            text-decoration: underline;
            text-decoration-thickness: 1px;
            background: transparent;
            font-family: Arial, sans-serif;
            outline: none;
            width: 90%;
            display: block;
            margin-top: 1px;
        }

        /* ── Screen only ── */
        .no-print {
            display: block;
        }

        .print-btn {
            margin-top: 20px;
            text-align: center;
        }

        .print-btn button {
            padding: 8px 28px;
            font-size: 11pt;
            cursor: pointer;
            background: #1d4ed8;
            color: #fff;
            border: none;
            border-radius: 4px;
        }

        .footer {
            margin-top: 10px;
            font-size: 8pt;
            color: #666;
            text-align: center;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            @page {
                size: A4 portrait;
                margin: 14mm 18mm;
            }
        }
    </style>
</head>

<body>

    {{-- ── Header ── --}}
    @php
        $logoSrc = $mode === 'pdf'
            ? 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('images/dost-logo.png')))
            : asset('images/dost-logo.png');
    @endphp
    <table style="width:100%;border:none;border-collapse:collapse;margin-bottom:10px;">
        <tr>
            <td style="width:64px;vertical-align:middle;border:none;padding:0 10px 0 0;">
                <img src="{{ $logoSrc }}" style="width:56px;height:56px;object-fit:contain;" alt="DOST">
            </td>
            <td style="vertical-align:middle;border:none;padding:0;">
                <div class="header">
                    <div class="header-agency">Department of Science and Technology</div>
                    <div class="header-title-row">
                        <div class="header-title">
                            LOCAL TRAVEL ORDER No. <span
                                style="border-bottom:1.5px solid #000;padding:0 40px 0 4px;">{{ $order->destination_scope === 'within_sdn' ? $order->travel_order_number : '' }}</span>
                        </div>
                        <div class="header-date">
                            Date: <span style="border-bottom:1px solid #000;padding:0 8px;">
                                {{ ($order->issued_at ?? $order->created_at)->format('F j, Y') }}
                            </span>
                        </div>
                    </div>
                    <div class="header-series" style="text-align:left;">Series of <strong
                            style="text-decoration:underline;">{{ ($order->issued_at ?? $order->created_at)->format('Y') }}</strong>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- ── Preamble ── --}}
    <div class="preamble">Authority to Travel is hereby granted to:</div>

    {{-- ── Personnel + Travel Details (single table for column alignment) ── --}}
    <table style="width:100%;table-layout:fixed;border-collapse:collapse;border:none;">
        <colgroup>
            <col style="width:33.33%">
            <col style="width:33.34%">
            <col style="width:33.33%">
        </colgroup>
        {{-- Personnel header --}}
        <tr>
            <th
                style="border:none;text-align:center;text-decoration:underline;font-size:9.5pt;padding:4px 6px;background:none;">
                NAME</th>
            <th
                style="border:none;text-align:center;text-decoration:underline;font-size:9.5pt;padding:4px 6px;background:none;">
                POSITION</th>
            <th
                style="border:none;text-align:center;text-decoration:underline;font-size:9.5pt;padding:4px 6px;background:none;">
                DIVISION/AGENCY</th>
        </tr>
        {{-- Personnel rows --}}
        @foreach ($order->passengers as $p)
            <tr>
                <td style="border:none;text-align:center;font-size:9.5pt;padding:3px 6px;">{{ strtoupper($p->name) }}
                </td>
                <td style="border:none;text-align:center;font-size:9.5pt;padding:3px 6px;">{{ $p->user?->position ?? $p->designation ?? '' }}
                </td>
                <td style="border:none;text-align:center;font-size:9.5pt;padding:3px 6px;">PSTO-SDN</td>
            </tr>
        @endforeach
        {{-- Spacer row between sections --}}
        <tr>
            <td colspan="3" style="border:none;padding:4px 0;"></td>
        </tr>
        {{-- Travel details header --}}
        <tr>
            <th
                style="border:none;text-align:center;text-decoration:underline;font-size:9.5pt;padding:4px 6px;background:none;">
                Destination</th>
            <th
                style="border:none;text-align:center;text-decoration:underline;font-size:9.5pt;padding:4px 6px;background:none;">
                Inclusive Date/s of Travel:</th>
            <th
                style="border:none;text-align:center;text-decoration:underline;font-size:9.5pt;padding:4px 6px;background:none;">
                Purpose(s) of the Travel:</th>
        </tr>
        {{-- Travel details row --}}
        <tr>
            <td style="border:none;text-align:center;font-size:9.5pt;padding:3px 6px;">{{ $order->destination }}</td>
            <td style="border:none;text-align:center;font-size:9.5pt;padding:3px 6px;">{{ $order->travelDateLabel() }}
            </td>
            <td style="border:none;text-align:center;font-size:9.5pt;padding:3px 6px;">{{ $order->purpose }}</td>
        </tr>
    </table>

    {{-- ── Travel Expenses ── --}}
    @php
        $fs = $order->fund_source ?? '';
        $isGeneral = $fs === 'General Fund';
        $isProject = str_starts_with($fs, 'Project Funds');
        $projectName = $isProject ? trim(substr($fs, strlen('Project Funds'))) : '';
        $isOthers = !$isGeneral && !$isProject && $fs !== '';
        $mark = fn(bool $cond, bool $fund) => $cond && $fund
            ? '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;X&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
            : '';
    @endphp

    <table class="expense-table">
        <thead>
            <tr>
                <th class="col-expense" rowspan="2">Travel Expenses to be Incurred</th>
                <th colspan="3">Appropriate/Fund to which travel expenses would be charged to:</th>
            </tr>
            <tr>
                <th class="col-fund">({{ $isGeneral ? 'x' : ' ' }}) General Fund</th>
                <th class="col-fund">
                    ({{ $isProject ? 'x' : ' ' }}) Project Funds
                    @if ($projectName)
                        <br><small>{{ $projectName }}</small>
                    @endif
                </th>
                <th class="col-fund">
                    ({{ $isOthers ? 'x' : ' ' }}) Others
                    @if ($isOthers)
                        <br><small>{{ $fs }}</small>
                    @else
                        <br><small>(e.g. Sponsor/Requesting Agency)</small>
                    @endif
                </th>
            </tr>
        </thead>
        <tbody>

            {{-- Actual --}}
            <tr class="row-group">
                <td>({{ $order->expense_actual ? 'x' : '' }}) Actual</td>
                <td class="mark-cell"></td>
                <td class="mark-cell"></td>
                <td class="mark-cell"></td>
            </tr>
            <tr class="row-sub">
                <td>Accommodation</td>
                <td class="mark-cell"><u>{!! $mark((bool) $order->expense_actual, $isGeneral) ?:
                    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</u></td>
                <td class="mark-cell"><u>{!! $mark((bool) $order->expense_actual, $isProject) ?:
                    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</u></td>
                <td class="mark-cell"><u>{!! $mark((bool) $order->expense_actual, $isOthers) ?:
                    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</u></td>
            </tr>
            <tr class="row-sub">
                <td>Meals/Food</td>
                <td class="mark-cell"><u>{!! $mark((bool) $order->expense_actual, $isGeneral) ?:
                    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</u></td>
                <td class="mark-cell"><u>{!! $mark((bool) $order->expense_actual, $isProject) ?:
                    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</u></td>
                <td class="mark-cell"><u>{!! $mark((bool) $order->expense_actual, $isOthers) ?:
                    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</u></td>
            </tr>
            <tr class="row-sub">
                <td>Incidental expenses</td>
                <td class="mark-cell"><u>{!! $mark((bool) $order->expense_actual, $isGeneral) ?:
                    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</u></td>
                <td class="mark-cell"><u>{!! $mark((bool) $order->expense_actual, $isProject) ?:
                    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</u></td>
                <td class="mark-cell"><u>{!! $mark((bool) $order->expense_actual, $isOthers) ?:
                    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</u></td>
            </tr>

            {{-- Per Diem --}}
            <tr class="row-group">
                <td>({{ $order->expense_per_diem ? 'x' : '' }}) Per Diem</td>
                <td class="mark-cell"></td>
                <td class="mark-cell"></td>
                <td class="mark-cell"></td>
            </tr>
            <tr class="row-sub">
                <td>Accommodation</td>
                <td class="mark-cell"><u>{!! $mark((bool) $order->expense_per_diem_accommodation, $isGeneral) ?:
                    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</u></td>
                <td class="mark-cell"><u>{!! $mark((bool) $order->expense_per_diem_accommodation, $isProject) ?:
                    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</u></td>
                <td class="mark-cell"><u>{!! $mark((bool) $order->expense_per_diem_accommodation, $isOthers) ?:
                    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</u></td>
            </tr>
            <tr class="row-sub">
                <td>Subsistence</td>
                <td class="mark-cell"><u>{!! $mark((bool) $order->expense_per_diem_subsistence, $isGeneral) ?:
                    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</u></td>
                <td class="mark-cell"><u>{!! $mark((bool) $order->expense_per_diem_subsistence, $isProject) ?:
                    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</u></td>
                <td class="mark-cell"><u>{!! $mark((bool) $order->expense_per_diem_subsistence, $isOthers) ?:
                    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</u></td>
            </tr>
            <tr class="row-sub">
                <td>Incidental expenses</td>
                <td class="mark-cell"><u>{!! $mark((bool) $order->expense_per_diem_incidental, $isGeneral) ?:
                    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</u></td>
                <td class="mark-cell"><u>{!! $mark((bool) $order->expense_per_diem_incidental, $isProject) ?:
                    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</u></td>
                <td class="mark-cell"><u>{!! $mark((bool) $order->expense_per_diem_incidental, $isOthers) ?:
                    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</u></td>
            </tr>

            {{-- Transportation --}}
            <tr class="row-group">
                <td>({{ $order->expense_transportation ? 'x' : '' }}) Transportation</td>
                <td class="mark-cell"></td>
                <td class="mark-cell"></td>
                <td class="mark-cell"></td>
            </tr>
            <tr class="row-sub">
                <td>Official Vehicle</td>
                <td class="mark-cell"><u>{!! $mark((bool) $order->expense_transportation_official_vehicle, $isGeneral) ?:
                    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</u></td>
                <td class="mark-cell"><u>{!! $mark((bool) $order->expense_transportation_official_vehicle, $isProject) ?:
                    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</u></td>
                <td class="mark-cell"><u>{!! $mark((bool) $order->expense_transportation_official_vehicle, $isOthers) ?:
                    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</u></td>
            </tr>
            <tr class="row-sub">
                <td>Public conveyance (Airplane, Bus, Taxi)</td>
                <td class="mark-cell"><u>{!! $mark((bool) $order->expense_transportation_public_conveyance, $isGeneral) ?:
                    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</u></td>
                <td class="mark-cell"><u>{!! $mark((bool) $order->expense_transportation_public_conveyance, $isProject) ?:
                    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</u></td>
                <td class="mark-cell"><u>{!! $mark((bool) $order->expense_transportation_public_conveyance, $isOthers) ?:
                    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</u></td>
            </tr>
            <tr class="row-sub">
                <td>Others</td>
                <td class="mark-cell"><u>{!! $mark((bool) $order->expense_transportation_others, $isGeneral) ?:
                    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</u></td>
                <td class="mark-cell"><u>{!! $mark((bool) $order->expense_transportation_others, $isProject) ?:
                    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</u></td>
                <td class="mark-cell"><u>{!! $mark((bool) $order->expense_transportation_others, $isOthers) ?:
                    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</u></td>
            </tr>

        </tbody>
    </table>

    {{-- ── Remarks ── --}}
    <table class="remarks-table">
        <colgroup>
            <col style="width:34%">
            <col style="width:66%">
        </colgroup>
        <tr>
            <td class="remarks-label">Remarks/Special Instructions:</td>
            <td class="remarks-line-cell">{!! $order->remarks ?: str_repeat('&nbsp;', 200) !!}</td>
        </tr>
        <tr>
            <td colspan="2" style="height:10px;border:none;padding:0;"></td>
        </tr>
        <tr>
            <td colspan="2" style="height:10px;border:none;padding:0;"></td>
        </tr>
        <tr>
            <td colspan="4" class="remarks-boilerplate">
                A report of your travel must be submitted to the Agency Head/Supervising Official within
                7 days of completion of travel, liquidation of cash advance should be in accordance with
                Executive Order No. 77, series of 2019: Prescribing Rules and Regulations, and Rates of
                Expenses and Allowances for Official Local and Foreign Travels of Government Personnel.
            </td>
        </tr>
    </table>

    {{-- ── Signature Block ── --}}
    <div class="sig-section">
        @if ($order->isOutsideSdn())
            {{-- Two signatories: Recommending Approval (PSTD) + Approved by (Regional Director) --}}
            <table class="sig-table">
                <colgroup>
                    <col style="width:50%">
                    <col style="width:50%">
                </colgroup>
                <tr>
                    <td style="text-align:left">
                        <div class="sig-label">Recommending Approval:</div>
                        <div class="sig-spacer"></div>
                        @if ($mode === 'screen')
                            <input type="text" class="sig-input" id="officer-input"
                                value="{{ $order->approving_officer }}" placeholder="Approving Officer" />
                            <input type="text" class="sig-position-input" id="position-input"
                                value="{{ $order->approving_position }}" placeholder="Position / Designation" />
                        @else
                            <span class="sig-name"><u>{{ $order->approving_officer }}</u></span>
                            <div class="sig-position">{{ $order->approving_position }}</div>
                        @endif
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td style="text-align:left">
                        <div class="sig-label">Approved by:</div>
                        <div class="sig-spacer"></div>
                        @if ($mode === 'screen')
                            <input type="text" class="sig-input" id="rd-input" value="Engr. Noel M. Ajoc"
                                placeholder="Regional Director" />
                            <input type="text" class="sig-position-input" value="Regional Director"
                                placeholder="Position" />
                        @else
                            <span class="sig-name"><u>Engr. Noel M. Ajoc</u></span>
                            <div class="sig-position">Regional Director</div>
                        @endif
                    </td>
                </tr>
            </table>
        @else
            {{-- Single signatory: Approved by (PSTD) --}}
            <div class="sig-label">Approved by:</div>
            <div class="sig-spacer"></div>
            @if ($mode === 'screen')
                <input type="text" class="sig-input" id="officer-input" value="{{ $order->approving_officer }}"
                    placeholder="Approving Officer" />
                <input type="text" class="sig-position-input" id="position-input"
                    value="{{ $order->approving_position }}" placeholder="Position / Designation" />
            @else
                <span class="sig-name">{{ $order->approving_officer }}</span>
                <div class="sig-position">{{ $order->approving_position }}</div>
            @endif
        @endif
    </div>

    @if ($mode === 'screen')
        <div class="no-print print-btn">
            <button onclick="window.print()">Print</button>
        </div>
        <div class="footer no-print">
            Generated by SDN-VRS &nbsp;|&nbsp; {{ now()->format('F j, Y \a\t h:i A') }}
        </div>
    @endif

</body>

</html>
