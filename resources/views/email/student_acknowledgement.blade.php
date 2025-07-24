
<!DOCTYPE html>
<html>
<head>
    <title>Your Student Application Has Been Submitted</title>
</head>
<body>
    <p>Dear {{ $studentName }},</p>

    <p>Thank you for submitting your student application form through our system. Your submission, with reference number <strong>{{ $applicationId }}</strong>, has been successfully received.</p>

    <p>For all inquiries regarding your application, please contact the college directly at <a href="mailto:{{ $collegeEmail }}">{{ $collegeEmail }}</a>. Please ensure you quote your reference number in all correspondence.</p>

    <p><strong>Kindly note:</strong> This is an unmonitored email address. Please do not reply to this email as your message will not be received or actioned.</p>

    <p>Sincerely,</p>
    <p><strong>{{ $collegeName }}</strong></p>
</body>
</html>
