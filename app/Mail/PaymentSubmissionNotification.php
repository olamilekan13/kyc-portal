<?php

namespace App\Mail;

use App\Models\KycSubmission;
use App\Models\FinalOnboarding;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class PaymentSubmissionNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $kycSubmission;
    public $finalOnboarding;

    /**
     * Create a new message instance.
     */
    public function __construct(KycSubmission $kycSubmission, FinalOnboarding $finalOnboarding)
    {
        $this->kycSubmission = $kycSubmission;
        $this->finalOnboarding = $finalOnboarding;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Payment Submission - ' . $this->kycSubmission->onboarding_token,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.payment.notification',
            with: [
                'kycSubmission' => $this->kycSubmission,
                'finalOnboarding' => $this->finalOnboarding,
                'applicantName' => $this->kycSubmission->submission_data['full_name'] ?? 'N/A',
                'applicantEmail' => $this->kycSubmission->submission_data['email'] ?? 'N/A',
                'partnershipModel' => $this->finalOnboarding->partnership_model_name,
                'partnershipPrice' => $this->finalOnboarding->partnership_model_price,
                'signupFee' => $this->finalOnboarding->signup_fee_amount,
                'totalAmount' => $this->finalOnboarding->total_amount,
                'paymentReference' => $this->finalOnboarding->signup_fee_reference ?? $this->finalOnboarding->model_fee_reference,
                'paymentNotes' => $this->finalOnboarding->payment_notes,
                'hasPaymentProof' => !empty($this->finalOnboarding->payment_proof),
                'onboardingToken' => $this->kycSubmission->onboarding_token,
                'submittedAt' => now()->format('F d, Y h:i A'),
            ]
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

        // Attach payment proof if exists
        try {
            if ($this->finalOnboarding->payment_proof && Storage::disk('local')->exists($this->finalOnboarding->payment_proof)) {
                $attachments[] = Attachment::fromStorageDisk('local', $this->finalOnboarding->payment_proof)
                    ->as('payment_proof_' . $this->kycSubmission->onboarding_token . '.' . pathinfo($this->finalOnboarding->payment_proof, PATHINFO_EXTENSION))
                    ->withMime(Storage::disk('local')->mimeType($this->finalOnboarding->payment_proof));

                \Log::info('Payment proof attached to email', [
                    'path' => $this->finalOnboarding->payment_proof,
                    'onboarding_token' => $this->kycSubmission->onboarding_token,
                ]);
            } else {
                \Log::warning('Payment proof file not found or not uploaded', [
                    'payment_proof_path' => $this->finalOnboarding->payment_proof,
                    'onboarding_token' => $this->kycSubmission->onboarding_token,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to attach payment proof to email', [
                'error' => $e->getMessage(),
                'payment_proof_path' => $this->finalOnboarding->payment_proof,
                'onboarding_token' => $this->kycSubmission->onboarding_token,
            ]);
            // Continue without attachment if it fails
        }

        return $attachments;
    }
}
