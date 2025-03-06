<!doctype html>
<html lang="en-US">

<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
    <title>Reset Password Email</title>
    <meta name="description" content="Reset Password Email Template.">
    <style type="text/css">
        body {
            margin: 0;
            padding: 0;
            font-family: 'Open Sans', sans-serif;
            background-color: #f2f3f8;
        }
        .container {
            max-width: 670px;
            margin: 0 auto;
            background: #fff;
            border-radius: 3px;
            padding: 35px;
            box-shadow: 0 6px 18px 0 rgba(0,0,0,.06);
        }
        h1 {
            font-family: 'Rubik', sans-serif;
            font-size: 32px;
            margin: 0;
            font-weight: 500;
        }
        p {
            font-size: 15px;
            line-height: 24px;
            margin: 0;
            color: #455056;
        }
        a {
            color: #1e1e2d;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .divider {
            border-bottom: 1px solid #cecece;
            width: 100px;
            margin: 29px 0 26px;
            display: inline-block;
            vertical-align: middle;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #455056;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Dear {{ $full_name }},</h1>
        <span class="divider"></span>
        <p>Congratulations on your newly created account with {{ $app_name }}! We're excited to have you on board.</p>
        <p>Your current password: <strong>{{ $password }}</strong></p>
        <p>To ensure the security of your account, please set a password by clicking on the following link:</p>
        <p><a href="{{ $password_reset_link }}">{{ $password_reset_link }}</a></p>
        <p>If you did not create an account or have any questions, please contact our support team at support@quickreview.app.</p>
        <p>Thank you for choosing {{ $app_name }}. We look forward to serving you.</p>
        <p>Best Regards,</p>
        <p>{{ $app_name }} Team</p>
    </div>
</body>

</html>
