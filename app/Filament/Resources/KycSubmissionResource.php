<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KycSubmissionResource\Pages;
use App\Models\KycForm;
use App\Models\KycSubmission;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components as FormFields;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class KycSubmissionResource extends Resource
{
    protected static ?string $model = KycSubmission::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'KYC Submissions';

    protected static ?string $modelLabel = 'KYC Submission';

    protected static ?string $pluralModelLabel = 'KYC Submissions';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        // No form needed - using custom view page
        return $schema->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Ref #')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('form.name')
                    ->label('Form Type')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state)))
                    ->color(fn (string $state): string => match ($state) {
                        KycSubmission::STATUS_PENDING => 'warning',
                        KycSubmission::STATUS_APPROVED => 'success',
                        KycSubmission::STATUS_DISAPPROVED, KycSubmission::STATUS_DECLINED => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('onboarding_status')
                    ->label('Onboarding')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state)))
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('finalOnboarding.partnership_model_name')
                    ->label('Partnership Model')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('finalOnboarding.payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state ? ucwords($state) : 'N/A')
                    ->color(fn (?string $state): string => match ($state) {
                        'completed' => 'success',
                        'partial' => 'warning',
                        'pending' => 'gray',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted At')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(KycSubmission::getStatuses())
                    ->multiple(),

                Tables\Filters\SelectFilter::make('kyc_form_id')
                    ->label('Form Type')
                    ->options(fn (): array => KycForm::pluck('name', 'id')->toArray())
                    ->searchable(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        FormFields\DatePicker::make('created_from')
                            ->label('Submitted From'),
                        FormFields\DatePicker::make('created_until')
                            ->label('Submitted Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn ($query, $date) => $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['created_until'],
                                fn ($query, $date) => $query->whereDate('created_at', '<=', $date)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators[] = 'From ' . \Carbon\Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators[] = 'Until ' . \Carbon\Carbon::parse($data['created_until'])->toFormattedDateString();
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (KycSubmission $record): bool =>
                        $record->status !== KycSubmission::STATUS_APPROVED
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Approve Submission')
                    ->modalDescription(fn (KycSubmission $record): string =>
                        "Are you sure you want to approve submission #{$record->id}? An approval email will be sent to the applicant."
                    )
                    ->modalIcon('heroicon-o-check-circle')
                    ->modalSubmitActionLabel('Yes, Approve')
                    ->action(function (KycSubmission $record) {
                        try {
                            $approveAction = app(\App\Actions\ApproveKycSubmissionAction::class);
                            $approveAction->execute($record, auth()->id());

                            \Filament\Notifications\Notification::make()
                                ->title('Submission approved')
                                ->body("Submission #{$record->id} has been approved and email sent to applicant.")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Approval failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('disapprove')
                    ->label('Disapprove')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (KycSubmission $record): bool =>
                        $record->status !== KycSubmission::STATUS_DISAPPROVED &&
                        $record->status !== KycSubmission::STATUS_DECLINED
                    )
                    ->form([
                        FormFields\Textarea::make('decline_reason')
                            ->label('Reason for Disapproval')
                            ->required()
                            ->rows(4)
                            ->minLength(10)
                            ->placeholder('Please provide a detailed reason for disapproving this submission...')
                            ->helperText('This reason will be sent to the applicant via email.'),
                    ])
                    ->modalHeading('Disapprove Submission')
                    ->modalDescription(fn (KycSubmission $record): string =>
                        "Please provide a reason for disapproving submission #{$record->id}. The reason will be sent to the applicant."
                    )
                    ->modalIcon('heroicon-o-x-circle')
                    ->modalSubmitActionLabel('Disapprove')
                    ->action(function (KycSubmission $record, array $data) {
                        try {
                            $declineAction = app(\App\Actions\DeclineKycSubmissionAction::class);
                            $declineAction->execute($record, auth()->id(), $data['decline_reason']);

                            \Filament\Notifications\Notification::make()
                                ->title('Submission disapproved')
                                ->body("Submission #{$record->id} has been disapproved and email sent to applicant.")
                                ->warning()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Disapproval failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (KycSubmission $record): string =>
                        route('filament.dashboard.resources.kyc-submissions.view', ['record' => $record])
                    ),

                Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Delete Submission')
                    ->modalDescription(fn (KycSubmission $record): string =>
                        "Are you sure you want to permanently delete submission #{$record->id}? This action cannot be undone."
                    )
                    ->action(function (KycSubmission $record) {
                        $record->delete();

                        \Filament\Notifications\Notification::make()
                            ->title('Submission deleted')
                            ->body("Submission #{$record->id} has been permanently deleted.")
                            ->success()
                            ->send();
                    }),
            ])
            ->recordUrl(fn (KycSubmission $record): string => route('filament.dashboard.resources.kyc-submissions.view', ['record' => $record]))
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKycSubmissions::route('/'),
            'view' => Pages\ViewKycSubmission::route('/{record}'),
        ];
    }
}
