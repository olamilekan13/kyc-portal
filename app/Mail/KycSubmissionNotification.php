<?php

namespace App\Mail;

use App\Models\KycSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KycSubmissionNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public KycSubmission $submission
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New KYC Submission - ' . $this->submission->form->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.kyc.submission',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        // Process submission data to extract base64 images
        foreach ($this->submission->submission_data as $key => $value) {
            // Check if value is a base64 encoded image
            if (is_string($value) && str_starts_with($value, 'data:image')) {
                try {
                    // Extract image data and mime type
                    preg_match('/^data:image\/(\w+);base64,/', $value, $matches);

                    if (isset($matches[1])) {
                        $extension = $matches[1]; // jpg, png, etc.
                        $base64Data = substr($value, strpos($value, ',') + 1);
                        $imageData = base64_decode($base64Data);

                        if ($imageData !== false) {
                            // Create a temporary file for the image
                            $filename = Str::slug($key) . '_' . $this->submission->id . '.' . $extension;

                            // Add as email attachment from raw data
                            $attachments[] = Attachment::fromData(fn () => $imageData, $filename)
                                ->withMime('image/' . $extension);

                            Log::info('Added image attachment to KYC notification email', [
                                'submission_id' => $this->submission->id,
                                'field' => $key,
                                'filename' => $filename,
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to process base64 image for email attachment', [
                        'submission_id' => $this->submission->id,
                        'field' => $key,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $attachments;
    }
}
