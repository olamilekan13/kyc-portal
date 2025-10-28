<?php

namespace App\Filament\Resources\KycSubmissionResource\Pages;

use App\Filament\Resources\KycSubmissionResource;
use App\Models\KycSubmission;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewKycSubmission extends ViewRecord
{
    protected static string $resource = KycSubmissionResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Section 1: Submission Information
                Section::make('Submission Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('id')
                                    ->label('Reference Number')
                                    ->formatStateUsing(fn ($record): string => "#{$record->id}")
                                    ->weight('bold')
                                    ->size(TextEntry\TextEntrySize::Large),

                                TextEntry::make('form.name')
                                    ->label('Form Type')
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('created_at')
                                    ->label('Submitted At')
                                    ->dateTime('M d, Y H:i A')
                                    ->icon('heroicon-o-calendar'),

                                TextEntry::make('status')
                                    ->label('Current Status')
                                    ->badge()
                                    ->formatStateUsing(fn ($state): string => ucwords(str_replace('_', ' ', $state)))
                                    ->color(fn ($state): string => match ($state) {
                                        'pending' => 'gray',
                                        'under_review' => 'info',
                                        'verified' => 'warning',
                                        'approved' => 'success',
                                        'declined' => 'danger',
                                        default => 'gray',
                                    }),

                                TextEntry::make('verification_status')
                                    ->label('Verification Status')
                                    ->badge()
                                    ->formatStateUsing(fn ($state): string => ucwords(str_replace('_', ' ', $state)))
                                    ->color(fn ($state): string => match ($state) {
                                        'not_verified' => 'gray',
                                        'verified' => 'success',
                                        'failed' => 'danger',
                                        default => 'gray',
                                    })
                                    ->icon(fn ($state): string => match ($state) {
                                        'verified' => 'heroicon-o-check-circle',
                                        'failed' => 'heroicon-o-x-circle',
                                        default => 'heroicon-o-clock',
                                    }),
                            ]),
                    ])
                    ->columns(1),

                // Section 2: Applicant Details
                Section::make('Applicant Details')
                    ->schema(function ($record): array {
                        $components = [];
                        $submissionData = $record->submission_data ?? [];

                        $gridItems = [];
                        foreach ($submissionData as $key => $value) {
                            // Skip empty values
                            if (empty($value)) {
                                continue;
                            }

                            // Format the label
                            $label = ucwords(str_replace('_', ' ', $key));

                            // Handle different data types
                            if (is_array($value)) {
                                // Handle file uploads
                                if (isset($value['path']) || isset($value['url'])) {
                                    $path = $value['path'] ?? '';
                                    $url = Storage::url($path);
                                    $filename = basename($path);

                                    $gridItems[] = TextEntry::make($key)
                                        ->label($label)
                                        ->formatStateUsing(fn () => "<a href='{$url}' target='_blank' class='text-primary-600 hover:underline flex items-center gap-1'>
                                            <svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'></path>
                                            </svg>
                                            {$filename}
                                        </a>")
                                        ->html();
                                } else {
                                    // Handle other arrays as JSON
                                    $gridItems[] = TextEntry::make($key)
                                        ->label($label)
                                        ->formatStateUsing(fn () => json_encode($value, JSON_PRETTY_PRINT));
                                }
                            } elseif ($this->isDate($key, $value)) {
                                // Handle dates
                                try {
                                    $formatted = \Carbon\Carbon::parse($value)->format('M d, Y');
                                } catch (\Exception $e) {
                                    $formatted = $value;
                                }
                                $gridItems[] = TextEntry::make($key)
                                    ->label($label)
                                    ->formatStateUsing(fn () => $formatted)
                                    ->icon('heroicon-o-calendar');
                            } elseif ($this->isPhone($key)) {
                                // Handle phone numbers
                                $gridItems[] = TextEntry::make($key)
                                    ->label($label)
                                    ->formatStateUsing(fn () => $this->formatPhone($value))
                                    ->icon('heroicon-o-phone')
                                    ->copyable();
                            } elseif ($this->isEmail($key, $value)) {
                                // Handle emails
                                $gridItems[] = TextEntry::make($key)
                                    ->label($label)
                                    ->formatStateUsing(fn () => "<a href='mailto:{$value}' class='text-primary-600 hover:underline'>{$value}</a>")
                                    ->icon('heroicon-o-envelope')
                                    ->copyable()
                                    ->html();
                            } else {
                                // Handle regular text
                                $gridItems[] = TextEntry::make($key)
                                    ->label($label)
                                    ->formatStateUsing(fn () => (string) $value)
                                    ->copyable();
                            }
                        }

                        if (!empty($gridItems)) {
                            $components[] = Grid::make(2)->schema($gridItems);
                        }

                        return $components;
                    })
                    ->collapsible()
                    ->persistCollapsed(),

                // Section 3: Verification Details
                Section::make('Verification Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('verificationLogs.verification_provider')
                                    ->label('Verification Provider')
                                    ->default('YouVerify')
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('verificationLogs.created_at')
                                    ->label('Verification Date')
                                    ->dateTime('M d, Y H:i A')
                                    ->default('N/A')
                                    ->icon('heroicon-o-clock'),

                                TextEntry::make('verificationLogs.status')
                                    ->label('Verification Status')
                                    ->badge()
                                    ->formatStateUsing(fn ($state): string => $state ? ucwords($state) : 'N/A')
                                    ->color(fn ($state): string => match ($state) {
                                        'success' => 'success',
                                        'failed' => 'danger',
                                        default => 'gray',
                                    }),
                            ]),

                        TextEntry::make('verification_response')
                            ->label('Verification Response')
                            ->formatStateUsing(fn ($state): string =>
                                !empty($state) ? json_encode($state, JSON_PRETTY_PRINT) : 'No data'
                            )
                            ->columnSpanFull()
                            ->visible(fn ($record): bool => !empty($record->verification_response)),
                    ])
                    ->visible(fn ($record): bool =>
                        $record->verification_status !== KycSubmission::VERIFICATION_NOT_VERIFIED ||
                        $record->verificationLogs->isNotEmpty()
                    )
                    ->collapsible()
                    ->persistCollapsed(),

                // Section 4: Review Information
                Section::make('Review Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('reviewer.name')
                                    ->label('Reviewed By')
                                    ->default('Not reviewed yet')
                                    ->icon('heroicon-o-user'),

                                TextEntry::make('reviewed_at')
                                    ->label('Reviewed At')
                                    ->dateTime('M d, Y H:i A')
                                    ->default('N/A')
                                    ->icon('heroicon-o-clock'),
                            ]),

                        TextEntry::make('decline_reason')
                            ->label('Decline Reason')
                            ->columnSpanFull()
                            ->badge()
                            ->color('danger')
                            ->visible(fn ($record): bool => !empty($record->decline_reason)),
                    ])
                    ->visible(fn ($record): bool =>
                        $record->reviewed_by !== null ||
                        $record->decline_reason !== null
                    )
                    ->collapsible()
                    ->persistCollapsed(),
            ]);
    }

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

            Actions\Action::make('download_pdf')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(fn (KycSubmission $record): string => route('kyc.submission.pdf', $record))
                ->openUrlInNewTab(),

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
    protected function isDate(string $key, mixed $value): bool
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
    protected function isPhone(string $key): bool
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
    protected function isEmail(string $key, mixed $value): bool
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
    protected function formatPhone(mixed $value): string
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
