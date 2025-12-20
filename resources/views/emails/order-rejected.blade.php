<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Payment Rejected</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius: 10px; padding: 30px; margin-bottom: 20px; text-align: center;">
        <div style="background-color: #ffffff; width: 80px; height: 80px; border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
            <svg style="width: 50px; height: 50px; color: #ef4444;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </div>
        <h1 style="color: #ffffff; margin: 0; font-size: 28px;">Payment Not Approved</h1>
        <p style="color: #fecaca; margin: 10px 0 0 0; font-size: 16px;">Your payment could not be verified</p>
    </div>

    <div style="background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 25px; margin-bottom: 20px;">
        <p style="font-size: 16px; color: #374151;">Dear {{ $order->partner->full_name }},</p>

        <p style="font-size: 16px; color: #374151;">We regret to inform you that the payment for order <strong>{{ $order->order_number }}</strong> could not be verified and has been rejected.</p>

        <h2 style="color: #1f2937; margin-top: 25px; border-bottom: 2px solid #ef4444; padding-bottom: 10px;">Order Details</h2>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;"><strong>Order Number:</strong></td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">{{ $order->order_number }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;"><strong>Partnership Model:</strong></td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">{{ $order->partnership_model_name }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;"><strong>Duration:</strong></td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">{{ $order->duration_months }} months</td>
            </tr>
            <tr>
                <td style="padding: 10px 0;"><strong>Order Amount:</strong></td>
                <td style="padding: 10px 0; text-align: right; font-size: 18px; color: #ef4444;"><strong>₦{{ number_format($order->total_amount, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <p style="margin: 0; color: #991b1b;"><strong>Possible Reasons:</strong></p>
        <ul style="margin: 10px 0; padding-left: 20px; color: #991b1b;">
            <li>Payment proof was unclear or incomplete</li>
            <li>Payment amount does not match order total</li>
            <li>Payment was made to incorrect account</li>
            <li>Bank transfer details could not be verified</li>
        </ul>
    </div>

    <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <p style="margin: 0; color: #92400e;"><strong>What Should You Do?</strong></p>
        <p style="margin: 10px 0 0 0; color: #92400e;">Please contact our support team or make a new payment with correct details. You can resubmit your payment proof from your dashboard.</p>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ route('partner.orders.show', ['order' => $order->id]) }}"
           style="display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 12px 30px; border-radius: 6px; font-weight: bold; margin-right: 10px;">
            View Order
        </a>
        <a href="{{ route('partner.dashboard') }}"
           style="display: inline-block; background-color: #6b7280; color: #ffffff; text-decoration: none; padding: 12px 30px; border-radius: 6px; font-weight: bold;">
            Go to Dashboard
        </a>
    </div>

    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #6b7280; font-size: 14px;">
        <p>Need help? Contact our support team</p>
        <p style="margin: 5px 0;">© {{ date('Y') }} DmplusPower. All rights reserved.</p>
    </div>
</body>
</html>
