<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Reservation Filed</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #1f2937; background: #f9fafb; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 32px auto; background: #fff; border-radius: 8px; border: 1px solid #e5e7eb; overflow: hidden; }
        .header { background: #1d4ed8; color: #fff; padding: 24px 32px; }
        .header h1 { margin: 0; font-size: 18px; font-weight: bold; }
        .header p { margin: 4px 0 0; font-size: 13px; opacity: .85; }
        .body { padding: 28px 32px; }
        .body p { margin: 0 0 12px; line-height: 1.6; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th { text-align: left; background: #f3f4f6; padding: 8px 12px; font-size: 12px; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
        td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; font-size: 13px; }
        .badge { display: inline-block; padding: 2px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600; background: #fef9c3; color: #92400e; }
        .btn { display: inline-block; margin-top: 20px; padding: 10px 24px; background: #1d4ed8; color: #fff; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 600; }
        .footer { padding: 16px 32px; background: #f9fafb; border-top: 1px solid #e5e7eb; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>New Reservation Filed</h1>
            <p>DOST-PSTO Surigao del Norte &mdash; SDN Vehicle Reservation System</p>
        </div>

        <div class="body">
            <p>A new vehicle reservation has been filed and is awaiting your review.</p>

            <table>
                <tr>
                    <th colspan="2">Reservation Details</th>
                </tr>
                <tr>
                    <td style="width:38%; color:#6b7280;">Ticket No.</td>
                    <td><strong>{{ $ticket->ticket_number }}</strong></td>
                </tr>
                <tr>
                    <td style="color:#6b7280;">Date Filed</td>
                    <td>{{ $ticket->date_filed->format('F j, Y') }}</td>
                </tr>
                <tr>
                    <td style="color:#6b7280;">Requested By</td>
                    <td>{{ $ticket->requester->name }}</td>
                </tr>
                <tr>
                    <td style="color:#6b7280;">Date of Travel</td>
                    <td>{{ $ticket->date_of_travel->format('F j, Y (l)') }}</td>
                </tr>
                <tr>
                    <td style="color:#6b7280;">Destination</td>
                    <td>{{ $ticket->destination }}</td>
                </tr>
                <tr>
                    <td style="color:#6b7280;">Purpose</td>
                    <td>{{ $ticket->purpose }}</td>
                </tr>
                <tr>
                    <td style="color:#6b7280;">Status</td>
                    <td><span class="badge">Pending</span></td>
                </tr>
            </table>

            <p>Please log in to the system to review and take action on this reservation.</p>

            <a href="{{ url('/reservations/' . $ticket->ticket_number) }}" class="btn">
                View Reservation
            </a>
        </div>

        <div class="footer">
            This is an automated notification from the SDN Vehicle Reservation System.
            Do not reply to this email.
        </div>
    </div>
</body>
</html>
