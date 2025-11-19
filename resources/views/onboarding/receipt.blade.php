<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {{ $kycSubmission->onboarding_token }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border: 2px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }

        .receipt-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .receipt-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .receipt-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .receipt-body {
            padding: 40px;
        }

        .info-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .info-section:last-child {
            border-bottom: none;
        }

        .info-section h2 {
            font-size: 18px;
            color: #667eea;
            margin-bottom: 15px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
        }

        .info-label {
            font-weight: 600;
            color: #666;
            flex: 0 0 40%;
        }

        .info-value {
            color: #333;
            flex: 0 0 55%;
            text-align: right;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .total-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }

        .receipt-footer {
            background: #f8f9fa;
            padding: 20px 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }

        .print-button {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            margin: 20px 0;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .print-button:hover {
            background: #5568d3;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .print-button {
                display: none;
            }

            .receipt-container {
                border: none;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <h1>Payment Receipt</h1>
            <p>Official payment confirmation for onboarding process</p>
        </div>

        <div class="receipt-body">
            <button class="print-button" onclick="window.print()">Print / Download PDF</button>

            <!-- Reference Information -->
            <div class="info-section">
                <h2>Reference Information</h2>
                <div class="info-row">
                    <div class="info-label">Onboarding Token:</div>
                    <div class="info-value">{{ $kycSubmission->onboarding_token }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Receipt Date:</div>
                    <div class="info-value">{{ now()->format('F d, Y h:i A') }}</div>
                </div>
                @if($finalOnboarding->signup_fee_reference)
                <div class="info-row">
                    <div class="info-label">Signup Fee Reference:</div>
                    <div class="info-value">{{ $finalOnboarding->signup_fee_reference }}</div>
                </div>
                @endif
                @if($finalOnboarding->model_fee_reference)
                <div class="info-row">
                    <div class="info-label">Partnership Fee Reference:</div>
                    <div class="info-value">{{ $finalOnboarding->model_fee_reference }}</div>
                </div>
                @endif
            </div>

            <!-- Customer Information -->
            <div class="info-section">
                <h2>Customer Information</h2>
                <div class="info-row">
                    <div class="info-label">Name:</div>
                    <div class="info-value">{{ $kycSubmission->submission_data['full_name'] ?? 'N/A' }}</div>
                </div>
                @if(isset($kycSubmission->submission_data['email']))
                <div class="info-row">
                    <div class="info-label">Email:</div>
                    <div class="info-value">{{ $kycSubmission->submission_data['email'] }}</div>
                </div>
                @endif
                @if(isset($kycSubmission->submission_data['phone']))
                <div class="info-row">
                    <div class="info-label">Phone:</div>
                    <div class="info-value">{{ $kycSubmission->submission_data['phone'] }}</div>
                </div>
                @endif
            </div>

            <!-- Payment Details -->
            <div class="info-section">
                <h2>Payment Details</h2>
                <div class="info-row">
                    <div class="info-label">Partnership Model:</div>
                    <div class="info-value">{{ $finalOnboarding->partnership_model_name }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Partnership Fee:</div>
                    <div class="info-value">₦{{ number_format($finalOnboarding->partnership_model_price, 2) }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Partnership Fee Status:</div>
                    <div class="info-value">
                        <span class="status-badge {{ $finalOnboarding->model_fee_paid ? 'status-paid' : 'status-pending' }}">
                            {{ $finalOnboarding->model_fee_paid ? 'PAID' : 'PENDING' }}
                        </span>
                    </div>
                </div>
                @if($finalOnboarding->model_fee_paid_at)
                <div class="info-row">
                    <div class="info-label">Partnership Fee Paid At:</div>
                    <div class="info-value">{{ \Carbon\Carbon::parse($finalOnboarding->model_fee_paid_at)->format('F d, Y h:i A') }}</div>
                </div>
                @endif

                <div class="info-row" style="margin-top: 20px;">
                    <div class="info-label">Signup Fee:</div>
                    <div class="info-value">₦{{ number_format($finalOnboarding->signup_fee_amount, 2) }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Signup Fee Status:</div>
                    <div class="info-value">
                        <span class="status-badge {{ $finalOnboarding->signup_fee_paid ? 'status-paid' : 'status-pending' }}">
                            {{ $finalOnboarding->signup_fee_paid ? 'PAID' : 'PENDING' }}
                        </span>
                    </div>
                </div>
                @if($finalOnboarding->signup_fee_paid_at)
                <div class="info-row">
                    <div class="info-label">Signup Fee Paid At:</div>
                    <div class="info-value">{{ \Carbon\Carbon::parse($finalOnboarding->signup_fee_paid_at)->format('F d, Y h:i A') }}</div>
                </div>
                @endif

                @if($finalOnboarding->solar_power)
                <div class="info-row" style="margin-top: 20px;">
                    <div class="info-label">Solar Power Package:</div>
                    <div class="info-value">₦{{ number_format($finalOnboarding->solar_power_amount, 2) }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Solar Power Status:</div>
                    <div class="info-value">
                        <span class="status-badge status-paid">
                            INCLUDED
                        </span>
                    </div>
                </div>
                @endif

                <div class="info-row" style="margin-top: 20px;">
                    <div class="info-label">Payment Method:</div>
                    <div class="info-value" style="text-transform: capitalize;">{{ str_replace('_', ' ', $finalOnboarding->payment_method) }}</div>
                </div>
            </div>

            <!-- Total Amount -->
            <div class="total-section">
                <div class="total-row">
                    <div>Total Amount:</div>
                    <div>₦{{ number_format($finalOnboarding->total_amount, 2) }}</div>
                </div>
            </div>

            @if($finalOnboarding->payment_notes)
            <!-- Additional Notes -->
            <div class="info-section" style="margin-top: 30px;">
                <h2>Additional Notes</h2>
                <p style="color: #666; padding: 10px 0;">{{ $finalOnboarding->payment_notes }}</p>
            </div>
            @endif
        </div>

        <div class="receipt-footer">
            <p>This is an official payment receipt. Please keep it for your records.</p>
            <p style="margin-top: 8px;">Generated on {{ now()->format('F d, Y \a\t h:i A') }}</p>
        </div>
    </div>

    <script>
        // Auto-print when opened in new window (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
