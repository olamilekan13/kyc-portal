<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Your Partner Dashboard</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #2563eb;
            color: white;
            padding: 30px 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #ffffff;
            padding: 30px;
            border: 1px solid #e5e7eb;
            border-top: none;
        }
        .credentials-box {
            background-color: #eff6ff;
            border: 2px solid #2563eb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .credential-item {
            margin: 15px 0;
        }
        .credential-label {
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
            display: block;
            margin-bottom: 5px;
        }
        .credential-value {
            background-color: white;
            padding: 12px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 16px;
            border: 1px solid #d1d5db;
        }
        .button {
            display: inline-block;
            padding: 14px 28px;
            background-color: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
        }
        .warning-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
        }
        .steps {
            margin: 20px 0;
        }
        .step {
            display: flex;
            margin: 15px 0;
        }
        .step-number {
            background-color: #2563eb;
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
            margin-right: 15px;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            border-radius: 0 0 8px 8px;
            border: 1px solid #e5e7eb;
            border-top: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0; font-size: 28px;">Welcome to Your Partner Dashboard!</h1>
    </div>

    <div class="content">
        <p>Dear {{ $partnerUser->first_name ?? 'Partner' }},</p>

        <p>Thank you for submitting your KYC information. Your application has been received and your partner account has been created successfully!</p>

        <div class="credentials-box">
            <h2 style="margin-top: 0; color: #1f2937; font-size: 18px;">Your Login Credentials</h2>

            <div class="credential-item">
                <span class="credential-label">Email Address:</span>
                <div class="credential-value">{{ $partnerUser->email }}</div>
            </div>

            <div class="credential-item">
                <span class="credential-label">Password:</span>
                <div class="credential-value">{{ $plainPassword }}</div>
            </div>
        </div>

        <div class="warning-box">
            <strong>⚠️ Important Security Notice</strong>
            <p style="margin: 5px 0 0 0;">Please save these credentials in a secure location. For your security, we recommend changing your password after your first login.</p>
        </div>

        <h3 style="color: #1f2937; margin-top: 30px;">Next Steps to Complete Your Partnership:</h3>

        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <div>
                    <strong>Log in to your dashboard</strong><br>
                    Use the credentials above to access your partner portal
                </div>
            </div>

            <div class="step">
                <div class="step-number">2</div>
                <div>
                    <strong>Complete the Partnership Form</strong><br>
                    Select your preferred partnership model from the available options
                </div>
            </div>

            <div class="step">
                <div class="step-number">3</div>
                <div>
                    <strong>Complete Payment</strong><br>
                    Choose your payment method and complete the payment to activate your partnership
                </div>
            </div>
        </div>

        <center>
            <a href="{{ $loginUrl }}" class="button" style="color: white;">Access Your Dashboard</a>
        </center>

        <h3 style="color: #1f2937; margin-top: 30px;">What Can You Do in Your Dashboard?</h3>
        <ul>
            <li>View your KYC submission status</li>
            <li>Complete your partnership form at your convenience</li>
            <li>Track your payment status</li>
            <li>View partnership details and documents</li>
            <li>Manage renewals when they're due</li>
        </ul>

        <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>

        <p>Best regards,<br>
        <strong>The Partnership Team</strong></p>
    </div>

    <div class="footer">
        <p style="margin: 0;">This is an automated email. Please do not reply to this message.</p>
        <p style="margin: 10px 0 0 0;">© {{ date('Y') }} KYC Portal. All rights reserved.</p>
    </div>
</body>
</html>
