<x-mail::message>
# New Payment Submission

A new payment has been submitted for final onboarding and requires your verification.

## Applicant Information
- **Name:** {{ $applicantName }}
- **Email:** {{ $applicantEmail }}
- **Onboarding Token:** {{ $onboardingToken }}
- **Submitted At:** {{ $submittedAt }}

## Partnership & Payment Details
- **Partnership Model:** {{ $partnershipModel }}
- **Partnership Fee:** ₦{{ number_format($partnershipPrice, 2) }}
- **Signup Fee:** ₦{{ number_format($signupFee, 2) }}
@if($finalOnboarding->solar_power)
- **Solar Power Package:** Yes - ₦{{ number_format($finalOnboarding->solar_power_amount, 2) }}
@endif
- **Total Amount:** ₦{{ number_format($totalAmount, 2) }}

## Payment Information
@if($paymentReference)
- **Payment Reference/Transaction ID:** {{ $paymentReference }}
@endif

@if($paymentNotes)
- **Payment Notes:** {{ $paymentNotes }}
@endif

@if($hasPaymentProof)
- **Payment Proof:** Attached to this email
@else
- **Payment Proof:** No file uploaded
@endif

## Onboarding Form Data
@if(!empty($finalOnboarding->form_data))
@foreach($finalOnboarding->form_data as $key => $value)
@if(!is_array($value))
- **{{ ucfirst(str_replace('_', ' ', $key)) }}:** {{ $value ?? 'N/A' }}
@endif
@endforeach
@else
*No additional form data submitted.*
@endif

<x-mail::button :url="url('/admin/final-onboarding')">
View Payment in Admin Panel
</x-mail::button>

**Action Required:** Please review and verify this payment submission.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
