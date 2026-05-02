<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .wrapper {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .header {
            background-color: #1a202c;
            padding: 24px 32px;
        }
        .header p {
            color: #a0aec0;
            margin: 0;
            font-size: 14px;
        }
        .body {
            padding: 32px;
        }
        .body h1 {
            color: #1a202c;
            font-size: 22px;
            margin-top: 0;
            margin-bottom: 16px;
        }
        .body p {
            color: #4a5568;
            line-height: 1.6;
            font-size: 15px;
            margin-bottom: 24px;
        }
        .btn {
            display: inline-block;
            background-color: #800020;
            color: #ffffff !important;
            padding: 12px 28px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }
        .footer {
            padding: 20px 32px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
        }
        .footer p {
            color: #a0aec0;
            font-size: 12px;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            @if($fromName)
                <p>{{ $fromName }}</p>
            @endif
        </div>

        <div class="body">
            <h1>{{ $title }}</h1>

            <p>{{ $body }}</p>

            @if($actionUrl)
                <a href="{{ $actionUrl }}" class="btn">{{ $actionText }}</a>
            @endif
        </div>

        <div class="footer">
            <p>You received this notification because you are registered on our platform.</p>
        </div>
    </div>
</body>
</html>
