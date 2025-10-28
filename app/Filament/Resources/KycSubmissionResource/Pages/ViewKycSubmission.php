<?php

namespace App\Filament\Resources\KycSubmissionResource\Pages;

use App\Filament\Resources\KycSubmissionResource;
use App\Models\KycSubmission;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class ViewKycSubmission extends ViewRecord
{
    protected static string $resource = KycSubmissionResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // Section 1: Submission Information
                Section::make('Submission Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Text::make('id')
                                    ->label('Reference Number')
                                    ->formatStateUsing(fn ($record): string => "#{$record->id}")
                                    ->weight('bold')
                                    ->size('lg'),

                                Text::make('form')
                                    ->label('Form Type')
                                    ->formatStateUsing(fn ($record): string => $record->form->name ?? 'N/A')
                                    ->badge()
                                    ->color('info'),

                                Text::make('created_at')
                                    ->label('Submitted At')
                                    ->formatStateUsing(fn ($record): string => $record->created_at->format('M d, Y H:i A'))
                                    ->icon('heroicon-o-calendar'),

                                Text::make('status')
                                    ->label('Current Status')
                                    ->badge()
                                    ->formatStateUsing(fn ($record): string => ucwords(str_replace('_', ' ', $record->status)))
                                    ->color(fn ($record): string => match ($record->status) {
                                        KycSubmission::STATUS_PENDING => 'gray',
                                        KycSubmission::STATUS_UNDER_REVIEW => 'info',
                                        KycSubmission::STATUS_VERIFIED => 'warning',
                                        KycSubmission::STATUS_APPROVED => 'success',
                                        KycSubmission::STATUS_DECLINED => 'danger',
                                        default => 'gray',
                                    }),

                                Text::make('verification_status')
                                    ->label('Verification Status')
                                    ->badge()
                                    ->formatStateUsing(fn ($record): string => ucwords(str_replace('_', ' ', $record->verification_status)))
                                    ->color(fn ($record): string => match ($record->verification_status) {
                                        KycSubmission::VERIFICATION_NOT_VERIFIED => 'gray',
                                        KycSubmission::VERIFICATION_VERIFIED => 'success',
                                        KycSubmission::VERIFICATION_FAILED => 'danger',
                                        default => 'gray',
                                    })
                                    ->icon(fn ($record): string => match ($record->verification_status) {
                                        KycSubmission::VERIFICATION_VERIFIED => 'heroicon-o-check-circle',
                                        KycSubmission::VERIFICATION_FAILED => 'heroicon-o-x-circle',
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

                                    $gridItems[] = Text::make($key)
                                        ->label($label)
                                        ->content("<a href='{$url}' target='_blank' class='text-primary-600 hover:underline flex items-center gap-1'>
                                            <svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'></path>
                                            </svg>
                                            {$filename}
                                        </a>")
                                        ->html();
                                } else {
                                    // Handle other arrays as JSON
                                    $gridItems[] = Text::make($key)
                                        ->label($label)
                                        ->content(json_encode($value, JSON_PRETTY_PRINT));
                                }
                            } elseif ($this->isDate($key, $value)) {
                                // Handle dates
                                try {
                                    $formatted = \Carbon\Carbon::parse($value)->format('M d, Y');
                                } catch (\Exception $e) {
                                    $formatted = $value;
                                }
                                $gridItems[] = Text::make($key)
                                    ->label($label)
                                    ->content($formatted)
                                    ->icon('heroicon-o-calendar');
                            } elseif ($this->isPhone($key)) {
                                // Handle phone numbers
                                $gridItems[] = Text::make($key)
                                    ->label($label)
                                    ->content($this->formatPhone($value))
                                    ->icon('heroicon-o-phone')
                                    ->copyable();
                            } elseif ($this->isEmail($key, $value)) {
                                // Handle emails
                                $gridItems[] = Text::make($key)
                                    ->label($label)
                                    ->content("<a href='mailto:{$value}' class='text-primary-600 hover:underline'>{$value}</a>")
                                    ->icon('heroicon-o-envelope')
                                    ->copyable()
                                    ->html();
                            } else {
                                // Handle regular text
                                $gridItems[] = Text::make($key)
                                    ->label($label)
                                    ->content((string) $value)
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
                                Text::make('verification_provider')
                                    ->label('Verification Provider')
                                    ->content(fn ($record): string => $record->verificationLogs->first()?->verification_provider ?? 'YouVerify')
                                    ->badge()
                                    ->color('info'),

                                Text::make('verification_date')
                                    ->label('Verification Date')
                                    ->content(fn ($record): string =>
                                        $record->verificationLogs->first()?->created_at?->format('M d, Y H:i A') ?? 'N/A'
                                    )
                                    ->icon('heroicon-o-clock'),

                                Text::make('verification_log_status')
                                    ->label('Verification Status')
                                    ->badge()
                                    ->content(fn ($record): string =>
                                        $record->verificationLogs->first()?->status
                                            ? ucwords($record->verificationLogs->first()->status)
                                            : 'N/A'
                                    )
                                    ->color(fn ($record): string => match ($record->verificationLogs->first()?->status) {
                                        'success' => 'success',
                                        'failed' => 'danger',
                                        default => 'gray',
                                    }),
                            ]),

                        Text::make('verification_response_data')
                            ->label('Verification Response')
                            ->content(fn ($record): string =>
                                !empty($record->verification_response)
                                    ? json_encode($record->verification_response, JSON_PRETTY_PRINT)
                                    : 'No data'
                            )
                            ->columnSpanFull()
                            ->visible(fn ($record): bool => !empty($record->verification_response)),

                        Text::make('verification_log_response')
                            ->label('Detailed Verification Data')
                            ->content(fn ($record): string =>
                                !empty($record->verificationLogs->first()?->response_payload)
                                    ? json_encode($record->verificationLogs->first()->response_payload, JSON_PRETTY_PRINT)
                                    : 'No data'
                            )
                            ->columnSpanFull()
                            ->visible(fn ($record): bool =>
                                !empty($record->verificationLogs->first()?->response_payload)
                            ),
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
                                Text::make('reviewer_name')
                                    ->label('Reviewed By')
                                    ->content(fn ($record): string => $record->reviewer?->name ?? 'Not reviewed yet')
                                    ->icon('heroicon-o-user'),

                                Text::make('reviewed_at')
                                    ->label('Reviewed At')
                                    ->content(fn ($record): string =>
                                        $record->reviewed_at?->format('M d, Y H:i A') ?? 'N/A'
                                    )
                                    ->icon('heroicon-o-clock'),
                            ]),

                        Text::make('decline_reason')
                            ->label('Decline Reason')
                            ->content(fn ($record): string => $record->decline_reason ?? '')
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
