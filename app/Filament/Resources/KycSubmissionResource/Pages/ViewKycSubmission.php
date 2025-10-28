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
            Actions\Action::make('verify')
                ->label('Verify with YouVerify')
                ->icon('heroicon-o-shield-check')
                ->color('info')
                ->visible(fn (KycSubmission $record): bool =>
                    $record->verification_status === KycSubmission::VERIFICATION_NOT_VERIFIED
                )
                ->requiresConfirmation()
                ->modalHeading('Verify Submission with YouVerify')
                ->modalDescription('This will send the submission data to YouVerify for identity verification.')
                ->modalIcon('heroicon-o-shield-check')
                ->action(function (KycSubmission $record) {
                    // TODO: Implement YouVerify API integration via YouVerifyService
                    // Example:
                    // $youVerifyService = app(YouVerifyService::class);
                    // $response = $youVerifyService->verifyIdentity($record);

                    $record->update([
                        'verification_status' => KycSubmission::VERIFICATION_VERIFIED,
                        'status' => KycSubmission::STATUS_UNDER_REVIEW,
                    ]);

                    // Create verification log
                    $record->verificationLogs()->create([
                        'verification_provider' => 'YouVerify',
                        'request_payload' => $record->submission_data,
                        'response_payload' => ['status' => 'success', 'message' => 'Verification completed'],
                        'status' => 'success',
                    ]);

                    Notification::make()
                        ->title('Verification initiated successfully')
                        ->body('The submission has been sent to YouVerify for verification.')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (KycSubmission $record): bool =>
                    $record->status === KycSubmission::STATUS_VERIFIED
                )
                ->requiresConfirmation()
                ->modalHeading('Approve Submission')
                ->modalDescription('Are you sure you want to approve this KYC submission?')
                ->modalIcon('heroicon-o-check-circle')
                ->modalSubmitActionLabel('Yes, Approve')
                ->action(function (KycSubmission $record) {
                    $record->update([
                        'status' => KycSubmission::STATUS_APPROVED,
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Submission approved successfully')
                        ->body("Submission #{$record->id} has been approved.")
                        ->success()
                        ->send();

                    return redirect()->route('filament.exit.resources.kyc-submissions.index');
                }),

            Actions\Action::make('decline')
                ->label('Decline')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (KycSubmission $record): bool =>
                    $record->status === KycSubmission::STATUS_VERIFIED
                )
                ->form([
                    Forms\Components\Textarea::make('decline_reason')
                        ->label('Decline Reason')
                        ->required()
                        ->rows(4)
                        ->placeholder('Please provide a detailed reason for declining this submission...')
                        ->helperText('This reason will be saved and may be shared with the applicant.'),
                ])
                ->modalHeading('Decline Submission')
                ->modalDescription('Please provide a reason for declining this KYC submission.')
                ->modalIcon('heroicon-o-x-circle')
                ->modalSubmitActionLabel('Decline Submission')
                ->action(function (KycSubmission $record, array $data) {
                    $record->update([
                        'status' => KycSubmission::STATUS_DECLINED,
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                        'decline_reason' => $data['decline_reason'],
                    ]);

                    Notification::make()
                        ->title('Submission declined')
                        ->body("Submission #{$record->id} has been declined.")
                        ->warning()
                        ->send();

                    return redirect()->route('filament.exit.resources.kyc-submissions.index');
                }),

            Actions\DeleteAction::make()
                ->visible(fn (KycSubmission $record): bool =>
                    $record->status === KycSubmission::STATUS_DECLINED
                )
                ->requiresConfirmation()
                ->modalHeading('Delete Submission')
                ->modalDescription('Are you sure you want to permanently delete this submission? This action cannot be undone.'),
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
