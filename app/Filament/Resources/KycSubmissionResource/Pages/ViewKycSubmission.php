<?php

namespace App\Filament\Resources\KycSubmissionResource\Pages;

use App\Filament\Resources\KycSubmissionResource;
use App\Models\KycSubmission;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewKycSubmission extends ViewRecord
{
    protected static string $resource = KycSubmissionResource::class;

    protected string $view = 'filament.resources.kyc-submission.pages.view-kyc-submission';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (KycSubmission $record): bool =>
                    $record->status === KycSubmission::STATUS_PENDING || $record->status === KycSubmission::STATUS_VERIFIED
                )
                ->requiresConfirmation()
                ->modalHeading('Approve Submission')
                ->modalDescription('Are you sure you want to approve this KYC submission? An approval email will be sent to the applicant.')
                ->modalIcon('heroicon-o-check-circle')
                ->modalSubmitActionLabel('Yes, Approve')
                ->action(function (KycSubmission $record) {
                    try {
                        $approveAction = app(\App\Actions\ApproveKycSubmissionAction::class);
                        $approveAction->execute($record, auth()->id());

                        Notification::make()
                            ->title('Submission approved successfully')
                            ->body("Submission #{$record->id} has been approved and email sent to applicant.")
                            ->success()
                            ->send();

                        return redirect()->route('filament.dashboard.resources.kyc-submissions.index');
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Approval failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('disapprove')
                ->label('Disapprove')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (KycSubmission $record): bool =>
                    $record->status === KycSubmission::STATUS_PENDING || $record->status === KycSubmission::STATUS_VERIFIED
                )
                ->form([
                    Forms\Components\Textarea::make('decline_reason')
                        ->label('Reason for Disapproval')
                        ->required()
                        ->rows(4)
                        ->minLength(10)
                        ->placeholder('Please provide a detailed reason for disapproving this submission...')
                        ->helperText('This reason will be sent to the applicant via email.'),
                ])
                ->modalHeading('Disapprove Submission')
                ->modalDescription('Please provide a reason for disapproving this KYC submission. The reason will be sent to the applicant.')
                ->modalIcon('heroicon-o-x-circle')
                ->modalSubmitActionLabel('Disapprove Submission')
                ->action(function (KycSubmission $record, array $data) {
                    try {
                        $declineAction = app(\App\Actions\DeclineKycSubmissionAction::class);
                        $declineAction->execute($record, auth()->id(), $data['decline_reason']);

                        Notification::make()
                            ->title('Submission disapproved')
                            ->body("Submission #{$record->id} has been disapproved and email sent to applicant.")
                            ->warning()
                            ->send();

                        return redirect()->route('filament.dashboard.resources.kyc-submissions.index');
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Disapproval failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('approve_payment')
                ->label('Approve Payment')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (KycSubmission $record): bool =>
                    $record->finalOnboarding &&
                    $record->finalOnboarding->payment_status === 'pending' &&
                    $record->finalOnboarding->payment_method !== null
                )
                ->requiresConfirmation()
                ->modalHeading('Approve Payment')
                ->modalDescription('Are you sure you want to approve this payment? This will activate the partnership.')
                ->action(function (KycSubmission $record) {
                    $finalOnboarding = $record->finalOnboarding;
                    if (!$finalOnboarding) return;

                    $durationMonths = $finalOnboarding->duration_months ?? 12;

                    $finalOnboarding->update([
                        'payment_status' => 'completed',
                        'signup_fee_paid' => true,
                        'model_fee_paid' => true,
                        'signup_fee_paid_at' => now(),
                        'model_fee_paid_at' => now(),
                        'partnership_start_date' => now()->toDateString(),
                        'partnership_end_date' => now()->addMonths($durationMonths)->toDateString(),
                        'renewal_status' => 'active',
                    ]);

                    // Update onboarding status
                    $record->update([
                        'onboarding_status' => 'completed',
                    ]);

                    // Update partner user payment_completed flag
                    if ($record->partnerUser) {
                        $record->partnerUser->update([
                            'payment_completed' => true,
                        ]);
                    }

                    Notification::make()
                        ->title('Payment approved')
                        ->body("Payment approved and partnership activated until {$finalOnboarding->partnership_end_date}")
                        ->success()
                        ->send();
                }),

            Actions\Action::make('reject_payment')
                ->label('Reject Payment')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (KycSubmission $record): bool =>
                    $record->finalOnboarding &&
                    $record->finalOnboarding->payment_status === 'pending' &&
                    $record->finalOnboarding->payment_method !== null
                )
                ->requiresConfirmation()
                ->modalHeading('Reject Payment')
                ->modalDescription('Are you sure you want to reject this payment? This will mark the payment as failed.')
                ->action(function (KycSubmission $record) {
                    $finalOnboarding = $record->finalOnboarding;
                    if (!$finalOnboarding) return;

                    $finalOnboarding->update([
                        'payment_status' => 'failed',
                    ]);

                    Notification::make()
                        ->title('Payment rejected')
                        ->body('Payment has been rejected.')
                        ->warning()
                        ->send();
                }),

            Actions\Action::make('view_payment_proof')
                ->label('View Payment Proof')
                ->icon('heroicon-o-photo')
                ->color('info')
                ->visible(fn (KycSubmission $record): bool =>
                    $record->finalOnboarding &&
                    $record->finalOnboarding->payment_proof !== null
                )
                ->url(fn (KycSubmission $record): string =>
                    Storage::disk('public')->url($record->finalOnboarding->payment_proof)
                )
                ->openUrlInNewTab(),

            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Delete Submission')
                ->modalDescription('Are you sure you want to permanently delete this submission? This action cannot be undone.')
                ->successRedirectUrl(route('filament.dashboard.resources.kyc-submissions.index')),
        ];
    }

    /**
     * Helper method to check if a field is likely a date
     */
    public function isDate(string $key, mixed $value): bool
    {
        $dateKeywords = ['date', 'birth', 'dob', 'issued', 'expiry', 'expires'];

        foreach ($dateKeywords as $keyword) {
            if (str_contains(strtolower($key), $keyword)) {
                return true;
            }
        }

        // Try to parse as date
        if (is_string($value)) {
            try {
                \Carbon\Carbon::parse($value);
                return preg_match('/^\d{4}-\d{2}-\d{2}/', $value) === 1;
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * Helper method to check if a field is likely a phone number
     */
    public function isPhone(string $key): bool
    {
        $phoneKeywords = ['phone', 'mobile', 'telephone', 'cell', 'contact'];

        foreach ($phoneKeywords as $keyword) {
            if (str_contains(strtolower($key), $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper method to check if a field is likely an email
     */
    public function isEmail(string $key, mixed $value): bool
    {
        if (str_contains(strtolower($key), 'email')) {
            return true;
        }

        if (is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        return false;
    }

    /**
     * Helper method to format phone numbers
     */
    public function formatPhone(mixed $value): string
    {
        if (!is_string($value)) {
            return (string) $value;
        }

        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $value);

        // Format based on length
        if (strlen($cleaned) === 10) {
            return preg_replace('/(\d{3})(\d{3})(\d{4})/', '($1) $2-$3', $cleaned);
        } elseif (strlen($cleaned) === 11) {
            return preg_replace('/(\d{1})(\d{3})(\d{3})(\d{4})/', '+$1 ($2) $3-$4', $cleaned);
        }

        return $value;
    }
}
