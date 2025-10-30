<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomePageSettingResource\Pages;
use App\Models\HomePageSetting;
use BackedEnum;
use Filament\Forms\Components as FormFields;
use Filament\Resources\Resource;
use Filament\Schemas\Components;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class HomePageSettingResource extends Resource
{
    protected static ?string $model = HomePageSetting::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Home Page';

    protected static ?string $modelLabel = 'Home Page Setting';

    protected static ?string $pluralModelLabel = 'Home Page Settings';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Components\Section::make('Page Content')
                    ->schema([
                        FormFields\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->label('Page Title')
                            ->placeholder('Welcome to KYC Portal')
                            ->default('Welcome to KYC Portal'),

                        FormFields\Textarea::make('subtitle')
                            ->rows(2)
                            ->label('Subtitle')
                            ->placeholder('Enter a brief subtitle')
                            ->columnSpanFull(),

                        FormFields\RichEditor::make('instructions')
                            ->label('Instructions / Content')
                            ->placeholder('Enter detailed instructions for users before they start the KYC process')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'bulletList',
                                'orderedList',
                                'h2',
                                'h3',
                                'link',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Components\Section::make('Call to Action Button')
                    ->schema([
                        FormFields\TextInput::make('button_text')
                            ->required()
                            ->maxLength(255)
                            ->label('Button Text')
                            ->placeholder('Start KYC Process')
                            ->default('Start KYC Process')
                            ->columnSpanFull(),

                        FormFields\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Enable or disable this home page configuration')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->label('Title')
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('button_text')
                    ->label('Button Text')
                    ->limit(30),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ])
                    ->placeholder('All'),
            ])
            ->defaultSort('updated_at', 'desc');
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
            'index' => Pages\ListHomePageSettings::route('/'),
            'create' => Pages\CreateHomePageSetting::route('/create'),
            'edit' => Pages\EditHomePageSetting::route('/{record}/edit'),
        ];
    }
}
