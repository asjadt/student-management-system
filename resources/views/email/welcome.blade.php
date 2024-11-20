<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        h1 {
            color: #444;
        }
        a {
            color: #007BFF;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, {{ $first_name }} {{ $last_name }}!</h1>
        <p>Your account has been successfully verified.</p>
        <p>If you ever forget your password, you can reset it by clicking the link below:</p>
        <p><a href="{{ $reset_password_link }}">Reset Password</a></p>
        <p>Thank you for being with us!</p>
    </div>
</body>
</html>
