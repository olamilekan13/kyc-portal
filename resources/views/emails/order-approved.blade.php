<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Approved</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 10px; padding: 30px; margin-bottom: 20px; text-align: center;">
        <div style="background-color: #ffffff; width: 80px; height: 80px; border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
            <svg style="width: 50px; height: 50px; color: #10b981;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h1 style="color: #ffffff; margin: 0; font-size: 28px;">Payment Approved!</h1>
        <p style="color: #d1fae5; margin: 10px 0 0 0; font-size: 16px;">Your order has been successfully approved and activated</p>
    </div>

    <div style="background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 25px; margin-bottom: 20px;">
        <p style="font-size: 16px; color: #374151;">Dear {{ $order->partner->full_name }},</p>

        <p style="font-size: 16px; color: #374151;">Great news! Your payment for order <strong>{{ $order->order_number }}</strong> has been approved and your order is now active.</p>

        <h2 style="color: #1f2937; margin-top: 25px; border-bottom: 2px solid #10b981; padding-bottom: 10px;">Order Details</h2>

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
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;"><strong>Start Date:</strong></td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">{{ $order->start_date ? $order->start_date->format('F d, Y') : 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;"><strong>End Date:</strong></td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">{{ $order->end_date ? $order->end_date->format('F d, Y') : 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 0;"><strong>Total Amount Paid:</strong></td>
                <td style="padding: 10px 0; text-align: right; font-size: 18px; color: #10b981;"><strong>₦{{ number_format($order->total_amount, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <div style="background-color: #d1fae5; border-left: 4px solid #10b981; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <p style="margin: 0; color: #065f46;"><strong>What's Next?</strong></p>
        <p style="margin: 10px 0 0 0; color: #065f46;">Your partnership is now active. Our team will contact you shortly with the next steps.</p>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ url('/partner/orders/' . $order->id) }}"
           style="display: inline-block; background-color: #10b981; color: #ffffff; text-decoration: none; padding: 12px 30px; border-radius: 6px; font-weight: bold;">
            View Order Details
        </a>
    </div>

    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #6b7280; font-size: 14px;">
        <p>Thank you for partnering with us!</p>
        <p style="margin: 5px 0;">© {{ date('Y') }} DmplusPower. All rights reserved.</p>
    </div>
</body>
</html>
