<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartnerUserResource\Pages;
use App\Models\PartnerUser;
use BackedEnum;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Components as Fields;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PartnerUserResource extends Resource
{
    protected static ?string $model = PartnerUser::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Partners';

    protected static ?string $modelLabel = 'Partner';

    protected static ?string $pluralModelLabel = 'Partners';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Fields\Section::make('Partner Information')
                    ->schema([
                        Fields\TextInput::make('first_name')
                            ->label('First Name')
                            ->maxLength(255),
                        Fields\TextInput::make('last_name')
                            ->label('Last Name')
                            ->maxLength(255),
                        Fields\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Fields\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Fields\Section::make('Account Status')
                    ->schema([
                        Fields\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'suspended' => 'Suspended',
                            ])
                            ->required()
                            ->default('active'),
                        Fields\Toggle::make('password_changed')
                            ->label('Password Changed')
                            ->default(false),
                    ])
                    ->columns(2),

                Fields\Section::make('Onboarding Progress')
                    ->schema([
                        Fields\Toggle::make('kyc_form_completed')
                            ->label('KYC Form Completed')
                            ->disabled(),
                        Fields\Toggle::make('onboarding_form_completed')
                            ->label('Onboarding Form Completed')
                            ->disabled(),
                        Fields\Toggle::make('payment_completed')
                            ->label('Payment Completed')
                            ->disabled(),
                    ])
                    ->columns(3),

                Fields\Section::make('Timestamps')
                    ->schema([
                        Fields\DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At')
                            ->disabled(),
                        Fields\DateTimePicker::make('last_accessed_at')
                            ->label('Last Accessed At')
                            ->disabled(),
                        Fields\DateTimePicker::make('created_at')
                            ->label('Created At')
                            ->disabled(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'suspended',
                    ])
                    ->sortable(),
                Tables\Columns\IconColumn::make('password_changed')
                    ->label('Pwd Changed')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('kyc_form_completed')
                    ->label('KYC')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('onboarding_form_completed')
                    ->label('Onboarding')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('payment_completed')
                    ->label('Payment')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('Progress')
                    ->suffix('%')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('last_accessed_at')
                    ->label('Last Login')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                    ]),
                Tables\Filters\TernaryFilter::make('password_changed')
                    ->label('Password Changed')
                    ->placeholder('All partners')
                    ->trueLabel('Password changed')
                    ->falseLabel('Using default password'),
                Tables\Filters\TernaryFilter::make('kyc_form_completed')
                    ->label('KYC Completed')
                    ->placeholder('All partners')
                    ->trueLabel('KYC completed')
                    ->falseLabel('KYC pending'),
                Tables\Filters\TernaryFilter::make('onboarding_form_completed')
                    ->label('Onboarding Completed')
                    ->placeholder('All partners')
                    ->trueLabel('Onboarding completed')
                    ->falseLabel('Onboarding pending'),
                Tables\Filters\TernaryFilter::make('payment_completed')
                    ->label('Payment Completed')
                    ->placeholder('All partners')
                    ->trueLabel('Payment completed')
                    ->falseLabel('Payment pending'),
            ])
            ->actions([
                Actions\Action::make('suspend')
                    ->label('Suspend')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (PartnerUser $record) => $record->update(['status' => 'suspended']))
                    ->visible(fn (PartnerUser $record) => $record->status === 'active'),
                Actions\Action::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (PartnerUser $record) => $record->update(['status' => 'active']))
                    ->visible(fn (PartnerUser $record) => $record->status === 'suspended'),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListPartnerUsers::route('/'),
            'create' => Pages\CreatePartnerUser::route('/create'),
            'view' => Pages\ViewPartnerUser::route('/{record}'),
            'edit' => Pages\EditPartnerUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}
