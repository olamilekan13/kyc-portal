<x-mail::message>
# KYC Submission Update

<x-mail::panel>
We regret to inform you that your KYC submission requires attention.
</x-mail::panel>

Dear Applicant,

Thank you for submitting your **{{ $formName }}** application. After careful review, we are unable to approve your submission at this time.

## Submission Details

- **Reference Number:** #{{ $referenceNumber }}
- **Reviewed By:** {{ $reviewerName }}
- **Review Date:** {{ $reviewDate }}
- **Status:** Requires Resubmission

## Reason for Decline

<x-mail::panel>
{{ $declineReason }}
</x-mail::panel>

## What You Can Do

To complete your verification, please address the issue(s) mentioned above and resubmit your application with the corrected information or documentation.

### Steps to Resubmit:

1. Review the decline reason carefully
2. Gather the required documents or correct the information
3. Submit a new KYC application through our portal
4. Ensure all information is accurate and complete

If you need clarification or assistance with your resubmission, our support team is here to help.

<x-mail::table>
| Contact Information | |
| :------------------ | :------ |
| **Email** | dmpluspower@digitalmediaplus.info |
| **Phone** | 08054629268 |
| **Hours** | Monday - Friday, 9 AM - 5 PM |
</x-mail::table>

We appreciate your understanding and look forward to assisting you with your verification.

Best regards,<br>
**DmplusPower Team**<br>
{{-- {{ config('app.name') }} --}}
</x-mail::message>

