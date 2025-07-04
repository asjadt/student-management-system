<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
</head>
<body>
    <h2>{{ $title }}</h2>

    <p>Dear {{ $business->name }},</p>

    <p>{{ $message_desc }}</p>

    <h3>📄 Passport Details:</h3>
    <ul>
        <li><strong>ID:</strong> {{ $student->student_id }}</li>
        <li><strong>Passport Number:</strong> {{ $student->passport_number }}</li>
        <li><strong>Expiry Date:</strong> {{ $student->passport_expiry_date }}</li>
    </ul>

    <h3>🏠 Student Details:</h3>
    <ul>
        <li><strong>Student Name:</strong> {{ $student->first_name . " " . $student->middle_name . " " . $student->last_name }}</li>
        <li><strong>Address:</strong> {{ $student->address }}, {{ $student->city }}, {{ $student->country }}</li>
        <li><strong>Email:</strong> {{ $student->email }}</li>
        <li><strong>Phone:</strong> {{ $student->contact_number }}</li>
    </ul>

    <p>You can review and update the document by clicking the link below:</p>
    <p><a href="{{ url('documents/' . $document->id) }}">View Document</a></p>

    <p>Best regards,</p>
    <p><strong>{{ config('app.name') }}</strong></p>
</body>
</html>
