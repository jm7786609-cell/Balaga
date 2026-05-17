<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reset Your Password</title>
    <style>
        body {
            background-color: #bff3db;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .email-container {
            max-width: 600px;
            margin: 50px auto;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #2bbf82;
            text-align: center;
        }

        p {
            color: #234c39;
            font-size: 16px;
            line-height: 1.5;
        }

        a.button {
            display: inline-block;
            background: #2bbf82;
            color: #fff;
            padding: 12px 25px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 20px;
        }

        a.button:hover {
            background: #28a776;
        }

        .footer {
            font-size: 12px;
            color: #555;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <h1>Pharmacy System</h1>
        <p>Hello,</p>
        <p>You requested to reset your password. Click the button below to set a new password. This link expires in
            <strong>60 minutes</strong>.
        </p>
        <p style="text-align:center;">
            <a href="{{ $url }}" class="button">Reset Password</a>
        </p>
        <p>If you did not request a password reset, please ignore this email.</p>
        <div class="footer">
            &copy; {{ date('Y') }} Pharmacy System. All rights reserved.
        </div>
    </div>
</body>

</html>
