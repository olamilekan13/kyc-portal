<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Mail\NewPartnerOrderMail;
use App\Mail\OrderPaymentSubmissionMail;
use App\Models\PartnerOrder;
use App\Models\PartnershipModel;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function create()
    {
        $partner = Auth::guard('partner')->user();

        // Check if partner has completed initial onboarding
        if (!$partner->payment_completed) {
            return redirect()->route('partner.dashboard')
                ->with('error', 'Please complete your initial onboarding before placing a new order.');
        }

        // Get active partnership models
        $partnershipModels = PartnershipModel::active()->get();

        // Get system settings
        $solarPowerEnabled = filter_var(SystemSetting::get('solar_power_enabled', 'true'), FILTER_VALIDATE_BOOLEAN);
        $solarPowerAmount = SystemSetting::get('solar_power_amount', 0);
        $solarPowerTitle = SystemSetting::get('solar_power_title', 'Do you want solar power?');
        $solarPowerDescription = SystemSetting::get('solar_power_description', 'Get reliable, clean energy for your operations with our solar power solution.');
        $solarPowerImage = SystemSetting::get('solar_power_image', '');

        Log::info('Partner creating new order', [
            'partner_id' => $partner->id,
            'email' => $partner->email,
        ]);

        return view('partner.orders.create', [
            'partner' => $partner,
            'partnershipModels' => $partnershipModels,
            'solarPowerEnabled' => $solarPowerEnabled,
            'solarPowerAmount' => $solarPowerAmount,
            'solarPowerTitle' => $solarPowerTitle,
            'solarPowerDescription' => $solarPowerDescription,
            'solarPowerImage' => $solarPowerImage,
        ]);
    }

    public function store(Request $request)
    {
        $partner = Auth::guard('partner')->user();

        $request->validate([
            'partnership_model_id' => 'required|exists:partnership_models,id',
            'solar_power' => 'nullable|boolean',
            'duration_months' => 'nullable|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $partnershipModel = PartnershipModel::findOrFail($request->partnership_model_id);
            $solarPower = $request->boolean('solar_power');
            $solarPowerAmount = $solarPower ? SystemSetting::get('solar_power_amount', 0) : 0;

            // No signup fee for additional orders
            $signupFeeAmount = 0;
            $subtotal = $partnershipModel->price + $solarPowerAmount;
            $totalAmount = $subtotal + $signupFeeAmount;

            $order = PartnerOrder::create([
                'partner_user_id' => $partner->id,
                'partnership_model_id' => $partnershipModel->id,
                'partnership_model_name' => $partnershipModel->name,
                'partnership_model_price' => $partnershipModel->price,
                'solar_power' => $solarPower,
                'solar_power_amount' => $solarPowerAmount,
                'signup_fee_amount' => $signupFeeAmount,
                'subtotal' => $subtotal,
                'total_amount' => $totalAmount,
                'duration_months' => $request->duration_months ?? 12,
                'status' => 'pending',
                'payment_status' => 'pending',
            ]);

            DB::commit();

            Log::info('Partner order created', [
                'partner_id' => $partner->id,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
            ]);

            // Send email notification to admin
            try {
                $adminEmail = SystemSetting::get('admin_notification_email', config('mail.from.address'));
                Mail::to($adminEmail)->send(new NewPartnerOrderMail($order));

                Log::info('New order email sent to admin', [
                    'order_id' => $order->id,
                    'admin_email' => $adminEmail,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send new order email', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return redirect()->route('partner.orders.payment', ['order' => $order->id])
                ->with('success', 'Order created successfully! Order Number: ' . $order->order_number);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating partner order', [
                'partner_id' => $partner->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to create order. Please try again.');
        }
    }

    public function payment(PartnerOrder $order)
    {
        $partner = Auth::guard('partner')->user();

        // Ensure order belongs to partner
        if ($order->partner_user_id !== $partner->id) {
            abort(403, 'Unauthorized access to order.');
        }

        // Check if already paid
        if ($order->payment_status === 'completed') {
            return redirect()->route('partner.orders.show', ['order' => $order->id])
                ->with('info', 'This order has already been paid.');
        }

        // Get payment settings
        $bankName = SystemSetting::get('bank_name', '');
        $bankAccountNumber = SystemSetting::get('bank_account_number', '');
        $bankAccountName = SystemSetting::get('bank_account_name', '');
        $paystackPublicKey = SystemSetting::get('paystack_public_key', '');

        Log::info('Partner accessing order payment', [
            'partner_id' => $partner->id,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ]);

        return view('partner.orders.payment', [
            'partner' => $partner,
            'order' => $order,
            'bankName' => $bankName,
            'bankAccountNumber' => $bankAccountNumber,
            'bankAccountName' => $bankAccountName,
            'paystackPublicKey' => $paystackPublicKey,
        ]);
    }

    public function processBankTransfer(Request $request, PartnerOrder $order)
    {
        $partner = Auth::guard('partner')->user();

        // Ensure order belongs to partner
        if ($order->partner_user_id !== $partner->id) {
            abort(403, 'Unauthorized access to order.');
        }

        $request->validate([
            'payment_proof' => 'nullable|image|mimes:jpeg,png,jpg,pdf|max:2048',
            'payment_notes' => 'nullable|string|max:500',
        ]);

        try {
            // Upload payment proof if provided
            $proofPath = null;
            if ($request->hasFile('payment_proof')) {
                $proofPath = $request->file('payment_proof')->store('payment-proofs', 'public');
            }

            $order->update([
                'payment_method' => 'bank_transfer',
                'payment_status' => 'pending',
                'payment_proof' => $proofPath,
                'payment_notes' => $request->payment_notes,
            ]);

            Log::info('Bank transfer submitted for order', [
                'partner_id' => $partner->id,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'has_proof' => $proofPath !== null,
            ]);

            // Send email notification to admin
            try {
                $adminEmail = SystemSetting::get('admin_notification_email', config('mail.from.address'));
                Mail::to($adminEmail)->send(new OrderPaymentSubmissionMail($order));
                Log::info('Order payment submission email sent to admin', [
                    'order_id' => $order->id,
                    'admin_email' => $adminEmail,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send order payment submission email', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $message = $proofPath
                ? 'Payment proof submitted successfully. Your order will be activated once payment is verified.'
                : 'Payment information submitted successfully. Please upload payment proof when available.';

            return redirect()->route('partner.orders.show', ['order' => $order->id])
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Error processing bank transfer for order', [
                'partner_id' => $partner->id,
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to submit payment proof. Please try again.');
        }
    }

    public function show(PartnerOrder $order)
    {
        $partner = Auth::guard('partner')->user();

        // Ensure order belongs to partner
        if ($order->partner_user_id !== $partner->id) {
            abort(403, 'Unauthorized access to order.');
        }

        return view('partner.orders.show', [
            'partner' => $partner,
            'order' => $order,
        ]);
    }

    public function verifyPaystackPayment(Request $request, PartnerOrder $order)
    {
        $partner = Auth::guard('partner')->user();

        // Ensure order belongs to partner
        if ($order->partner_user_id !== $partner->id) {
            abort(403, 'Unauthorized access to order.');
        }

        $reference = $request->query('reference');

        if (!$reference) {
            return redirect()->route('partner.orders.payment', ['order' => $order->id])
                ->with('error', 'Payment reference not found. Please try again.');
        }

        try {
            // Verify payment with Paystack
            $paystackSecretKey = SystemSetting::get('paystack_secret_key', '');

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://api.paystack.co/transaction/verify/{$reference}",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer {$paystackSecretKey}",
                    "Cache-Control: no-cache",
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                throw new \Exception("cURL Error: " . $err);
            }

            $result = json_decode($response);

            if ($result->status && $result->data->status === 'success') {
                // Payment successful
                $order->update([
                    'payment_method' => 'paystack',
                    'payment_status' => 'completed',
                    'payment_reference' => $reference,
                    'paid_at' => now(),
                ]);

                // Activate the order
                $order->activate();

                Log::info('Paystack payment verified successfully', [
                    'partner_id' => $partner->id,
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'reference' => $reference,
                ]);

                return redirect()->route('partner.orders.show', ['order' => $order->id])
                    ->with('success', 'Payment successful! Your order has been activated. Order Number: ' . $order->order_number);
            } else {
                throw new \Exception('Payment verification failed');
            }

        } catch (\Exception $e) {
            Log::error('Error verifying Paystack payment', [
                'partner_id' => $partner->id,
                'order_id' => $order->id,
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('partner.orders.payment', ['order' => $order->id])
                ->with('error', 'Payment verification failed. Please contact support if you were charged.');
        }
    }

    public function index()
    {
        $partner = Auth::guard('partner')->user();
        $orders = $partner->orders()->latest()->get();

        Log::info('Partner viewing order history', [
            'partner_id' => $partner->id,
            'order_count' => $orders->count(),
        ]);

        return view('partner.orders.index', [
            'partner' => $partner,
            'orders' => $orders,
        ]);
    }
}
