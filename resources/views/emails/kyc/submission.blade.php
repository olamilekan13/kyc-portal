<x-mail::message>
# New KYC Submission Received

<x-mail::panel>
A new KYC form submission has been received and requires your review.
</x-mail::panel>

## Submission Overview

<x-mail::table>
| Field | Value |
|:------|:------|
| **Form Name** | {{ $submission->form->name }} |
| **Submission ID** | #{{ $submission->id }} |
| **Status** | {{ ucfirst($submission->status) }} |
| **Submitted At** | {{ $submission->created_at->format('F d, Y \a\t h:i A') }} |
</x-mail::table>

## Applicant Information

@foreach($submission->submission_data as $key => $value)
@if(str_ends_with($key, '_verified'))
{{-- Skip verification flags as they are displayed with their parent fields --}}
@elseif(is_array($value) && isset($value['original_name']))
**{{ ucfirst(str_replace('_', ' ', $key)) }}:** 📎 {{ $value['original_name'] }}

@elseif(is_string($value) && str_starts_with($value, 'data:image'))
**{{ ucfirst(str_replace('_', ' ', $key)) }}:** 📷 Image Attached (See attachments)

@elseif(!is_array($value) && strlen($value) < 100)
**{{ ucfirst(str_replace('_', ' ', $key)) }}:** {{ $value }}
@if(isset($submission->submission_data[$key . '_verified']) && $submission->submission_data[$key . '_verified'])
✅ **Verified: True**
@endif

@elseif(!is_array($value))
**{{ ucfirst(str_replace('_', ' ', $key)) }}:** {{ Str::limit($value, 80) }}
@if(isset($submission->submission_data[$key . '_verified']) && $submission->submission_data[$key . '_verified'])
✅ **Verified: True**
@endif

@endif
@endforeach

@php
    $hasAttachedImages = collect($submission->submission_data)->contains(function($value) {
        return is_string($value) && str_starts_with($value, 'data:image');
    });
@endphp

@if($hasAttachedImages)
---

**Note:** Images from the submission are attached to this email for your review.
@endif

<x-mail::button :url="config('app.url') . '/dashboard/kyc-submissions/' . $submission->id">
Review Submission in Admin Panel
</x-mail::button>

---

**Next Steps:**
- Review the submission details in the admin panel
- Verify the uploaded documents and images
- Approve or decline the submission with appropriate notes

Thank you,<br>
**DmplusPower Team**
</x-mail::message>
