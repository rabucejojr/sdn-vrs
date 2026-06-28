<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Page Not Found | SDN-VRS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
            text-align: center;
        }

        .logo {
            width: 72px;
            height: 72px;
            margin-bottom: 24px;
            opacity: 0.85;
        }

        .agency {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            margin-bottom: 4px;
        }

        .system-name {
            font-size: 14px;
            font-weight: 700;
            color: #1e3a5f;
            margin-bottom: 36px;
        }

        .code {
            font-size: 80px;
            font-weight: 800;
            color: #2563eb;
            line-height: 1;
            margin-bottom: 12px;
        }

        .title {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
        }

        .message {
            font-size: 14px;
            color: #64748b;
            max-width: 400px;
            line-height: 1.6;
            margin-bottom: 32px;
        }

        .btn {
            display: inline-block;
            background: #2563eb;
            color: #fff;
            padding: 11px 28px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .btn:hover { background: #1d4ed8; }

        .divider {
            width: 48px;
            height: 3px;
            background: #2563eb;
            border-radius: 2px;
            margin: 0 auto 28px;
        }

        .footer {
            margin-top: 48px;
            font-size: 11px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <img src="/images/dost-logo.png" alt="DOST Logo" class="logo" />

    <p class="agency">PSTO Surigao del Norte</p>
    <p class="system-name">SDN Vehicle Reservation System</p>

    <div class="divider"></div>

    <div class="code">404</div>
    <h1 class="title">Page Not Found</h1>
    <p class="message">
        The page you are looking for does not exist or has been moved.
        Please check the URL or return to the dashboard.
    </p>

    <a href="/dashboard" class="btn">Go to Dashboard</a>

    <p class="footer">
        &copy; {{ date('Y') }} PSTO Surigao del Norte &mdash; SDN-VRS
    </p>

</body>
</html>
