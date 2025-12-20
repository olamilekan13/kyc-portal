<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Payment Submitted</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; border-radius: 10px; padding: 30px; margin-bottom: 20px;">
        <h1 style="color: #2563eb; margin-top: 0;">Order Payment Submitted</h1>
        <p style="font-size: 16px; color: #666;">A partner has submitted payment for their order. Please review and approve the payment.</p>
    </div>

    <div style="background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 25px; margin-bottom: 20px;">
        <h2 style="color: #1f2937; margin-top: 0; border-bottom: 2px solid #2563eb; padding-bottom: 10px;">Order Details</h2>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;"><strong>Order Number:</strong></td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">{{ $order->order_number }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;"><strong>Partner Name:</strong></td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">{{ $order->partner->full_name }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;"><strong>Partner Email:</strong></td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">{{ $order->partner->email }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;"><strong>Partnership Model:</strong></td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">{{ $order->partnership_model_name }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;"><strong>Payment Method:</strong></td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">{{ ucwords(str_replace('_', ' ', $order->payment_method)) }}</td>
            </tr>
            @if($order->payment_proof)
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;"><strong>Payment Proof:</strong></td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">Uploaded</td>
            </tr>
            @endif
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;"><strong>Total Amount:</strong></td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; text-align: right; font-size: 18px; color: #2563eb;"><strong>₦{{ number_format($order->total_amount, 2) }}</strong></td>
            </tr>
            <tr>
                <td style="padding: 10px 0;"><strong>Submission Date:</strong></td>
                <td style="padding: 10px 0; text-align: right;">{{ now()->format('F d, Y h:i A') }}</td>
            </tr>
        </table>

        @if($order->payment_notes)
        <div style="margin-top: 20px; padding: 15px; background-color: #f3f4f6; border-radius: 6px;">
            <strong>Payment Notes:</strong>
            <p style="margin: 10px 0 0 0;">{{ $order->payment_notes }}</p>
        </div>
        @endif
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ route('filament.dashboard.resources.partner-orders.view', ['record' => $order->id]) }}"
           style="display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 12px 30px; border-radius: 6px; font-weight: bold;">
            Review Payment in Admin Panel
        </a>
    </div>

    <div style="background-color: #dcfce7; border-left: 4px solid #16a34a; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <p style="margin: 0; color: #166534;"><strong>Action Required:</strong> Please review the payment details and approve or reject the payment in the admin panel.</p>
    </div>

    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #6b7280; font-size: 14px;">
        <p>This is an automated email from your KYC Portal system.</p>
        <p style="margin: 5px 0;">© {{ date('Y') }} DmplusPower. All rights reserved.</p>
    </div>
</body>
</html>
