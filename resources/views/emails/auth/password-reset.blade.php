<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reset Your Password</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1a365d; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .btn { display: inline-block; padding: 12px 24px; background: #1a365d; color: white; text-decoration: none; border-radius: 5px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Shah Sports</h1>
            <p>Password Reset Request</p>
        </div>
        
        <div class="content">
            <p>You are receiving this email because we received a password reset request for your account.</p>
            
            <p style="text-align: center; margin: 30px 0;">
                <a href="{{ $resetUrl }}" class="btn">Reset Password</a>
            </p>
            
            <p>This password reset link will expire in 60 minutes.</p>
            
            <p>If you did not request a password reset, no further action is required.</p>
        </div>
        
        <div class="footer">
            <p>Shah Sports - Your Sports Equipment Destination</p>
            <p>If you're having trouble clicking the button, copy and paste this URL into your browser:</p>
            <p style="word-break: break-all;">{{ $resetUrl }}</p>
        </div>
    </div>
</body>
</html>
