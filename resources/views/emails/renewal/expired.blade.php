<x-mail::message>
# Partnership Expired

Dear {{ $partnerName }},

We regret to inform you that your **{{ $partnershipModel }}** partnership has expired on **{{ $expiryDate }}**.

## What This Means
Your partnership benefits and services are no longer active. To continue enjoying our services, please renew your partnership.

## Renew Your Partnership

Don't worry! You can still renew your partnership and restore your benefits:

<x-mail::button :url="$renewalUrl" color="success">
Renew Partnership Now
</x-mail::button>

## Need Help?
If you have any questions about renewal or need assistance, please don't hesitate to contact our support team.

We value your partnership and hope to continue serving you!

Thanks,<br>
{{ config('app.name') }}

<x-mail::subcopy>
If you're having trouble clicking the button, copy and paste this URL into your browser: [{{ $renewalUrl }}]({{ $renewalUrl }})
</x-mail::subcopy>
</x-mail::message>
