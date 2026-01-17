<!--
    resources/views/emails/update_link.blade.php

    This email is sent when a parent requests an update to an existing registration.
    It contains a unique link allowing them to modify their info and re-pay if needed.
-->
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Update Your Registration</title>
</head>
<body style="font-family: Arial, sans-serif;">

<!-- Email Header -->
<h1 style="color: #333;">Update Your Registration</h1>
<p>Dear Parent,</p>

<!-- Instructions -->
<p>We received your request to update your school registration details. To ensure everything is accurate, please click the link below to access and modify your information:</p>

<!-- Unique Update Link -->
<p>
    <a href="{{ $url }}" style="color: #1e90ff; text-decoration: underline;">
        Update Your Registration Details
    </a>
</p>

<!-- Warning about expiration -->
<p><em>Important:</em> For security reasons, this link will expire in a few hours. If the link is invalid or has expired, please visit the Retrieve Registration page to request a new one.</p>

<!-- Closing -->
<p>If you did not request this update, please ignore this email.</p>

<p style="margin-top: 20px;">Best regards,<br>
Dhamma and Sinhala Language School Management Team</p>

</body>
</html>
