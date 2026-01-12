<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $campaign->subject }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1a365d; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Shah Sports</h1>
        </div>
        
        <div class="content">
            {!! $content !!}
        </div>
        
        <div class="footer">
            <p>Shah Sports - Your Sports Equipment Destination</p>
            <p><a href="{{ config('app.frontend_url') }}/unsubscribe?email={{ urlencode($recipient->email) }}">Unsubscribe</a></p>
        </div>
        
        <!-- Tracking pixel -->
        <img src="{{ $trackingUrl }}" width="1" height="1" style="display:none;" alt="">
    </div>
</body>
</html>
