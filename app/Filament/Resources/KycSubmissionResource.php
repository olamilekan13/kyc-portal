<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KycSubmissionResource\Pages;
use App\Models\KycForm;
use App\Models\KycSubmission;
use BackedEnum;
use Filament\Forms\Components as FormFields;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components;
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

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Components\Section::make('Submission Details')
                    ->schema([
                        Components\Placeholder::make('id')
                            ->label('Reference Number')
                            ->content(fn (KycSubmission $record): string => "#{$record->id}"),

                        Components\Placeholder::make('form.name')
                            ->label('Form Type')
                            ->content(fn (KycSubmission $record): string => $record->form->name ?? 'N/A'),

                        Components\Placeholder::make('status')
                            ->label('Status')
                            ->content(fn (KycSubmission $record): string => ucwords(str_replace('_', ' ', $record->status))),

                        Components\Placeholder::make('verification_status')
                            ->label('Verification Status')
                            ->content(fn (KycSubmission $record): string => ucwords(str_replace('_', ' ', $record->verification_status))),

                        Components\Placeholder::make('created_at')
                            ->label('Submitted At')
                            ->content(fn (KycSubmission $record): string => $record->created_at->format('M d, Y H:i')),

                        Components\KeyValue::make('submission_data')
                            ->label('Submitted Data')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Components\Section::make('Review Information')
                    ->schema([
                        Components\Placeholder::make('reviewer.name')
                            ->label('Reviewed By')
                            ->content(fn (KycSubmission $record): string => $record->reviewer->name ?? 'Not reviewed yet'),

                        Components\Placeholder::make('reviewed_at')
                            ->label('Reviewed At')
                            ->content(fn (KycSubmission $record): string => $record->reviewed_at?->format('M d, Y H:i') ?? 'N/A'),

                        Components\Placeholder::make('decline_reason')
                            ->label('Decline Reason')
                            ->content(fn (KycSubmission $record): string => $record->decline_reason ?? 'N/A')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->visible(fn (KycSubmission $record): bool => $record->reviewed_by !== null || $record->decline_reason !== null),

                Components\Section::make('Verification Response')
                    ->schema([
                        Components\KeyValue::make('verification_response')
                            ->label('Verification Data')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (KycSubmission $record): bool => !empty($record->verification_response)),
            ]);
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

                Tables\Columns\TextColumn::make('applicant_name')
                    ->label('Applicant Name')
                    ->searchable()
                    ->formatStateUsing(function (KycSubmission $record): string {
                        $data = $record->submission_data;
                        // Try common name field variations
                        return $data['full_name']
                            ?? $data['name']
                            ?? ($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')
                            ?? 'N/A';
                    })
                    ->sortable(false),

                Tables\Columns\TextColumn::make('applicant_email')
                    ->label('Applicant Email')
                    ->searchable()
                    ->formatStateUsing(function (KycSubmission $record): string {
                        $data = $record->submission_data;
                        return $data['email'] ?? $data['email_address'] ?? 'N/A';
                    })
                    ->sortable(false),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state)))
                    ->color(fn (string $state): string => match ($state) {
                        KycSubmission::STATUS_PENDING => 'gray',
                        KycSubmission::STATUS_UNDER_REVIEW => 'info',
                        KycSubmission::STATUS_VERIFIED => 'warning',
                        KycSubmission::STATUS_APPROVED => 'success',
                        KycSubmission::STATUS_DECLINED => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('verification_status')
                    ->label('Verification')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state)))
                    ->color(fn (string $state): string => match ($state) {
                        KycSubmission::VERIFICATION_NOT_VERIFIED => 'gray',
                        KycSubmission::VERIFICATION_VERIFIED => 'success',
                        KycSubmission::VERIFICATION_FAILED => 'danger',
                        default => 'gray',
                    }),

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

                Tables\Filters\SelectFilter::make('verification_status')
                    ->label('Verification Status')
                    ->options(KycSubmission::getVerificationStatuses())
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
