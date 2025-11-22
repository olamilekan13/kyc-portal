<x-mail::message>
# KYC Submission Approved

<x-mail::panel>
**Congratulations!** Your KYC submission has been approved.
</x-mail::panel>

Dear Applicant,

We are pleased to inform you that your **{{ $formName }}** submission has been successfully reviewed and approved.

## Submission Details

- **Reference Number:** #{{ $referenceNumber }}
- **Reviewed By:** {{ $reviewerName }}
- **Review Date:** {{ $reviewDate }}
- **Status:** Approved

Your identity verification has been completed successfully. You can now proceed with the next steps in your application process.

## What's Next?

Your account is now fully verified. You may start using all available services without restrictions.

If you have any questions or need assistance, please don't hesitate to contact our support team.

<x-mail::table>
| Contact Information | |
| :------------------ | :------ |
| **Email** | dmpluspower@digitalmediaplus.info |
| **Phone** | 08054629268 |
</x-mail::table>

Thank you for completing your KYC verification.

Best regards,<br>
**DmplusPower Team**<br>
{{-- {{ config('app.name') }} --}}
</x-mail::message>
