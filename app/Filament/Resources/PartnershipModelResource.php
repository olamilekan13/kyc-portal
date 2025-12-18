<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartnershipModelResource\Pages;
use App\Models\PartnershipModel;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components as FormFields;
use Filament\Resources\Resource;
use Filament\Schemas\Components;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PartnershipModelResource extends Resource
{
    protected static ?string $model = PartnershipModel::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Partnership Models';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Components\Section::make('Partnership Model Details')
                    ->schema([
                        FormFields\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Model Name')
                            ->placeholder('e.g., Bronze Partnership, Silver Partnership'),

                        FormFields\RichEditor::make('description')
                            ->label('Description')
                            ->placeholder('Describe the benefits and features of this partnership model')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'link',
                                'table',
                                'h2',
                                'h3',
                                'undo',
                                'redo',
                            ])
                            ->columnSpanFull(),

                        FormFields\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('₦')
                            ->label('Price')
                            ->placeholder('0.00')
                            ->step(0.01)
                            ->minValue(0),

                        FormFields\Select::make('duration_months')
                            ->required()
                            ->options([
                                0 => 'No Renewal Required',
                                1 => '1 Month',
                                3 => '3 Months',
                                6 => '6 Months',
                                12 => '1 Year (12 Months)',
                                24 => '2 Years (24 Months)',
                            ])
                            ->default(12)
                            ->label('Duration')
                            ->helperText('Select "No Renewal Required" for models like Basic that don\'t need renewal'),

                        FormFields\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->label('Sort Order')
                            ->helperText('Lower numbers appear first in the dropdown'),

                        FormFields\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Only active models will be shown to users'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable()
                    ->html()
                    ->formatStateUsing(fn ($state) => strip_tags($state)),

                Tables\Columns\TextColumn::make('price')
                    ->money('NGN')
                    ->sortable()
                    ->label('Price'),

                Tables\Columns\TextColumn::make('duration_months')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => match($state) {
                        0 => 'No Renewal',
                        1 => '1 Month',
                        3 => '3 Months',
                        6 => '6 Months',
                        12 => '1 Year',
                        24 => '2 Years',
                        default => $state . ' Months',
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable()
                    ->label('Order'),

                Tables\Columns\TextColumn::make('onboardingSubmissions_count')
                    ->counts('onboardingSubmissions')
                    ->label('Subscribers')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All models')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Actions\BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-circle')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
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
            'index' => Pages\ListPartnershipModels::route('/'),
            'create' => Pages\CreatePartnershipModel::route('/create'),
            'edit' => Pages\EditPartnershipModel::route('/{record}/edit'),
        ];
    }
}
