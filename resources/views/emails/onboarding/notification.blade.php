<x-mail::message>
# New Final Onboarding Submission

A new final onboarding form has been submitted and requires your attention.

## Applicant Information
- **Name:** {{ $applicantName }}
- **Email:** {{ $applicantEmail }}
- **Onboarding Token:** {{ $onboardingToken }}
- **Submitted At:** {{ $submittedAt }}

## Partnership & Payment Details
- **Partnership Model:** {{ $partnershipModel }}
@if($finalOnboarding->solar_power)
- **Solar Power Package:** Yes - ₦{{ number_format($finalOnboarding->solar_power_amount, 2) }}
@endif
- **Total Amount:** ₦{{ number_format($totalAmount, 2) }}
- **Payment Method:** {{ $paymentMethod }}
- **Payment Status:** {{ ucfirst($finalOnboarding->payment_status) }}

@if($finalOnboarding->payment_method === 'bank_transfer')
## Bank Transfer Information
@if($finalOnboarding->signup_fee_reference || $finalOnboarding->model_fee_reference)
- **Payment Reference:** {{ $finalOnboarding->signup_fee_reference ?? $finalOnboarding->model_fee_reference }}
@endif
@if($finalOnboarding->payment_notes)
- **Payment Notes:** {{ $finalOnboarding->payment_notes }}
@endif

**Note:** This payment requires admin verification.
@endif

## Form Data Submitted
@if(!empty($finalOnboarding->form_data))
@foreach($finalOnboarding->form_data as $key => $value)
@if(!is_array($value))
- **{{ ucfirst(str_replace('_', ' ', $key)) }}:** {{ $value }}
@endif
@endforeach
@endif

<x-mail::button :url="route('filament.dashboard.resources.kyc-submissions.view', ['record' => $kycSubmission->id])">
View Submission in Admin Panel
</x-mail::button>

Please review this submission and take appropriate action.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
