<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .otp-code {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin: 20px 0;
            text-align: center;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            text-align: center;
            color: #999999;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        @if(isset($purpose) && $purpose === 'registration')
            <h1>Email Verification OTP</h1>
        @else
            <h1>Password Reset OTP</h1>
        @endif
    </div>
    <div style="padding: 20px">
        <p>Hello,</p>
        
        @if(isset($purpose) && $purpose === 'registration')
            <p>You requested to verify your email address. Please use the following OTP code:</p>
        @else
            <p>You requested to reset your password. Please use the following OTP code:</p>
        @endif

        <div class="otp-code">{{ $otp }}</div>

        @if(isset($purpose) && $purpose === 'registration')
            <p>Please enter this code on the verification page. This code is valid for 5 minutes.</p>
        @else
            <p>Please enter this code on the password reset page. This code is valid for 5 minutes.</p>
        @endif
        
        <p><strong>If you didn't request this, please ignore this email.</strong></p>

        <p>Thank you,<br> The Shah Sports Team</p>

        <div class="footer">
            <small>This an auto generated email. Never reply to this email.</small> <br>
            &copy; {{ date('Y') }} Shah Sports. All rights reserved.
        </div>
    </div>
</div>

</body>
</html>
