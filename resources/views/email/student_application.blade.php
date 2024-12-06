<!DOCTYPE html>
<html>
<head>
    <title>New Student Application Submission</title>
</head>
<body>
    <p>Dear {{ $businessOwnerName }},</p>

    <p>I hope this message finds you well.</p>

    <p>We have received a new online application from a prospective student. Below are the details of the submission:</p>

    <ul>
        <li><strong>Student Name:</strong> {{ $studentName }}</li>
        <li><strong>Application Date:</strong> {{ $applicationDate }}</li>
        <li><strong>Course Applied For:</strong> {{ $courseAppliedFor }}</li>
        <li><strong>Application ID:</strong> {{ $applicationId }}</li>
        <li><strong>Student Email:</strong> {{ $studentEmail }}</li>
    </ul>

    <p>The student has successfully completed all required sections of the application.</p>

    <p>Best regards,</p>
    <p>Student Management System</p>
    <p><strong>{{ $collegeName }}</strong></p>
</body>
</html>
