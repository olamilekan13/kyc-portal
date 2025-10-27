<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KycFormResource\Pages;
use App\Models\KycForm;
use App\Models\KycFormField;
use BackedEnum;
use Filament\Forms\Components as FormFields;
use Filament\Resources\Resource;
use Filament\Schemas\Components;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

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
                Components\Section::make('Form Details')
                    ->schema([
                        FormFields\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Form Name')
                            ->placeholder('Enter form name'),

                        FormFields\Textarea::make('description')
                            ->rows(3)
                            ->label('Description')
                            ->placeholder('Enter form description (optional)')
                            ->columnSpanFull(),

                        FormFields\Toggle::make('status')
                            ->label('Active')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Enable or disable this form'),

                        FormFields\Hidden::make('created_by')
                            ->default(auth()->id()),
                    ])
                    ->columns(2),

                Components\Section::make('Form Fields')
                    ->schema([
                        FormFields\Repeater::make('fields')
                            ->relationship('fields')
                            ->schema([
                                FormFields\Select::make('field_type')
                                    ->options(KycFormField::FIELD_TYPES)
                                    ->required()
                                    ->searchable()
                                    ->label('Field Type')
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        // Auto-generate field_name from field_label if not manually edited
                                        $fieldLabel = $get('field_label');
                                        if ($fieldLabel && !$get('field_name')) {
                                            $set('field_name', Str::slug($fieldLabel, '_'));
                                        }
                                    }),

                                FormFields\TextInput::make('field_label')
                                    ->required()
                                    ->label('Display Label')
                                    ->placeholder('e.g., First Name')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        // Auto-generate field_name from field_label
                                        if ($state) {
                                            $set('field_name', Str::slug($state, '_'));
                                        }
                                    }),

                                FormFields\TextInput::make('field_name')
                                    ->required()
                                    ->label('Field Name/ID')
                                    ->placeholder('e.g., first_name')
                                    ->helperText('Use lowercase, no spaces (e.g., first_name)')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        // Clean the field name: lowercase, no spaces, alphanumeric + underscores only
                                        if ($state) {
                                            $cleaned = Str::lower($state);
                                            $cleaned = str_replace(' ', '_', $cleaned);
                                            $cleaned = preg_replace('/[^a-z0-9_]/', '', $cleaned);
                                            $set('field_name', $cleaned);
                                        }
                                    })
                                    ->rule('regex:/^[a-z0-9_]+$/'),

                                FormFields\Toggle::make('is_required')
                                    ->label('Required Field')
                                    ->default(false)
                                    ->inline(false),

                                FormFields\KeyValue::make('options')
                                    ->label('Dropdown Options')
                                    ->keyLabel('Value')
                                    ->valueLabel('Label')
                                    ->helperText('Add options for the dropdown menu')
                                    ->visible(fn (Get $get): bool => $get('field_type') === 'select')
                                    ->reorderable()
                                    ->addActionLabel('Add Option')
                                    ->columnSpanFull(),

                                FormFields\TagsInput::make('validation_rules')
                                    ->label('Additional Validation Rules')
                                    ->placeholder('e.g., min:3, max:50')
                                    ->helperText('Enter Laravel validation rules (one per tag)')
                                    ->columnSpanFull(),
                            ])
                            ->orderable('order')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['field_label'] ?? 'New Field')
                            ->defaultItems(0)
                            ->addActionLabel('Add Form Field')
                            ->reorderable()
                            ->cloneable()
                            ->columnSpanFull()
                            ->columns(2),
                    ])
                    ->description('Define the fields that will appear in this KYC form')
                    ->collapsible()
                    ->persistCollapsed(),
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
