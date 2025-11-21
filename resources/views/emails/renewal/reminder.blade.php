<x-mail::message>
# Partnership Renewal Reminder

Dear {{ $partnerName }},

Your **{{ $partnershipModel }}** partnership is expiring soon!

## Partnership Details
- **Current Plan:** {{ $partnershipModel }}
- **Expiry Date:** {{ $expiryDate }}
- **Days Remaining:** {{ $daysUntilExpiry }} day(s)

@if($daysUntilExpiry <= 3)
**URGENT:** Your partnership expires very soon. Please renew immediately to avoid service interruption.
@endif

## Renewal Amount
**{{ $partnershipPrice }}** (Partnership Fee)

## How to Renew

Click the button below to renew your partnership:

<x-mail::button :url="$renewalUrl" color="primary">
Renew Partnership Now
</x-mail::button>

## Payment Options

**Option 1: Pay Online**
Use our secure online payment via Paystack (Card/Bank Transfer)

**Option 2: Bank Transfer**
@if($bankName && $bankAccountNumber)
- **Bank:** {{ $bankName }}
- **Account Number:** {{ $bankAccountNumber }}
- **Account Name:** {{ $bankAccountName }}
@else
Contact us for bank details.
@endif

---

If you have any questions, please contact our support team.

Thanks,<br>
{{ config('app.name') }}

<x-mail::subcopy>
If you're having trouble clicking the "Renew Partnership Now" button, copy and paste the URL below into your web browser: [{{ $renewalUrl }}]({{ $renewalUrl }})
</x-mail::subcopy>
</x-mail::message>
