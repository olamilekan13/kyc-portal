<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SystemSettingResource\Pages;
use App\Models\SystemSetting;
use BackedEnum;
use Filament\Forms\Components as FormFields;
use Filament\Resources\Resource;
use Filament\Schemas\Components;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class SystemSettingResource extends Resource
{
    protected static ?string $model = SystemSetting::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'System Settings';

    protected static ?string $modelLabel = 'System Setting';

    protected static ?string $pluralModelLabel = 'System Settings';

    protected static ?int $navigationSort = 99;

    protected static UnitEnum | string | null $navigationGroup = 'Settings';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Components\Section::make('Setting Details')
                    ->schema([
                        FormFields\TextInput::make('key')
                            ->required()
                            ->maxLength(255)
                            ->label('Setting Key')
                            ->placeholder('e.g., kyc_notification_email')
                            ->disabled(fn ($record) => $record !== null)
                            ->helperText('Unique identifier for this setting. Cannot be changed after creation.'),

                        FormFields\Select::make('type')
                            ->required()
                            ->options([
                                'string' => 'Text',
                                'boolean' => 'True/False',
                                'integer' => 'Number',
                                'json' => 'JSON',
                            ])
                            ->default('string')
                            ->label('Value Type')
                            ->reactive()
                            ->helperText('Select the type of value this setting stores.'),

                        FormFields\Textarea::make('value')
                            ->required()
                            ->rows(3)
                            ->label('Value')
                            ->placeholder('Enter the setting value')
                            ->columnSpanFull(),

                        FormFields\Textarea::make('description')
                            ->rows(2)
                            ->label('Description')
                            ->placeholder('Describe what this setting controls')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable()
                    ->label('Setting Key')
                    ->weight('medium')
                    ->copyable(),

                Tables\Columns\TextColumn::make('value')
                    ->label('Value')
                    ->limit(50)
                    ->wrap()
                    ->copyable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'primary' => 'string',
                        'success' => 'boolean',
                        'warning' => 'integer',
                        'info' => 'json',
                    ]),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(40)
                    ->wrap(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'string' => 'Text',
                        'boolean' => 'True/False',
                        'integer' => 'Number',
                        'json' => 'JSON',
                    ])
                    ->placeholder('All Types'),
            ])
            ->defaultSort('key', 'asc');
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
            'index' => Pages\ListSystemSettings::route('/'),
            'create' => Pages\CreateSystemSetting::route('/create'),
            'edit' => Pages\EditSystemSetting::route('/{record}/edit'),
        ];
    }
}
