<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your Account Has Been Created</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #4F46E5; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .credentials { background-color: white; padding: 15px; border-left: 4px solid #4F46E5; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
        .button { display: inline-block; padding: 10px 20px; background-color: #4F46E5; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to HR Management System</h1>
        </div>

        <div class="content">
            <p>Dear {{ $user->name }},</p>

            <p>Your account has been successfully created in the HR Management System. You can now log in using the following credentials:</p>

            <div class="credentials">
                <strong>Email:</strong> {{ $user->email }}<br>
                <strong>Password:</strong> {{ $password }}
            </div>

            <p><strong>Important:</strong> Please change your password after your first login for security purposes.</p>

            <p>You can access the system at: <a href="{{ url('/') }}">{{ url('/') }}</a></p>

            <p>If you have any questions or need assistance, please contact your administrator.</p>

            <p>Best regards,<br>
            HR Management System Team</p>
        </div>

        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
