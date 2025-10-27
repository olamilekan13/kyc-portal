<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KycFormResource\Pages;
use App\Models\KycForm;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class KycFormResource extends Resource
{
    protected static ?string $model = KycForm::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'KYC Forms';

    protected static ?string $modelLabel = 'KYC Form';

    protected static ?string $pluralModelLabel = 'KYC Forms';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Form Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Form Name')
                            ->placeholder('Enter form name'),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->label('Description')
                            ->placeholder('Enter form description (optional)')
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('status')
                            ->label('Active')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Enable or disable this form'),

                        Forms\Components\Hidden::make('created_by')
                            ->default(auth()->id()),
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
                    ->label('Form Name')
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('Status')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ])
                    ->placeholder('All Forms'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListKycForms::route('/'),
            'create' => Pages\CreateKycForm::route('/create'),
            'view' => Pages\ViewKycForm::route('/{record}'),
            'edit' => Pages\EditKycForm::route('/{record}/edit'),
        ];
    }
}
