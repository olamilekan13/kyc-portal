<x-mail::message>
# New KYC Submission Received

A new KYC form submission has been received and requires your review.

## Submission Details

**Form Name:** {{ $submission->form->name }}

**Submission ID:** #{{ $submission->id }}

**Status:** {{ ucfirst($submission->status) }}

**Submitted At:** {{ $submission->created_at->format('F d, Y h:i A') }}

## Submitted Information

@foreach($submission->submission_data as $key => $value)
@if(is_array($value) && isset($value['original_name']))
**{{ ucfirst(str_replace('_', ' ', $key)) }}:** {{ $value['original_name'] }} (File)
@elseif(!is_array($value))
**{{ ucfirst(str_replace('_', ' ', $key)) }}:** {{ $value }}
@endif
@endforeach

<x-mail::button :url="config('app.url') . '/admin/kyc-submissions/' . $submission->id">
Review Submission
</x-mail::button>

Please log in to the admin panel to review and process this submission.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
