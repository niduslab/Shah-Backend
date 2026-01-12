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
        <h1>Verify Your Email</h1>
    </div>
    <div style="padding: 20px">
        <p>Hello,</p>
        <p>Thank you for registering your shop. To verify your email address, please use the following OTP code:</p>

        <div class="otp-code">{{ $otp }}</div>

        <p>Please enter this code on the email verification page. This code is valid for 5 minutes.</p>

        <p>Thank you,<br> The NidusCart Team</p>

        <div class="footer">
            <small>This an auto generated email. Never reply to this email.</small> <br>
            &copy; {{ date('Y') }} NidusCart. All rights reserved.
        </div>
    </div>
</div>

</body>
</html>
