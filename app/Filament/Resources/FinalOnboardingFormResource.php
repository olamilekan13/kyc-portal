<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FinalOnboardingFormResource\Pages;
use App\Models\FinalOnboardingForm;
use App\Models\FinalOnboardingFormField;
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

class FinalOnboardingFormResource extends Resource
{
    protected static ?string $model = FinalOnboardingForm::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Final Onboarding Forms';

    protected static ?string $modelLabel = 'Final Onboarding Form';

    protected static ?string $pluralModelLabel = 'Final Onboarding Forms';

    protected static ?int $navigationSort = 3;

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
                            ->placeholder('Enter form name')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                // Auto-generate slug from form name if slug is empty
                                if ($state && !$get('slug')) {
                                    $set('slug', FinalOnboardingForm::generateSlug($state));
                                }
                            }),

                        FormFields\Hidden::make('slug')
                            ->default(fn (Get $get) => $get('name') ? FinalOnboardingForm::generateSlug($get('name')) : null)
                            ->dehydrateStateUsing(fn ($state, Get $get) => $state ?: FinalOnboardingForm::generateSlug($get('name'))),

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

                        FormFields\Toggle::make('is_default')
                            ->label('Set as Default Form')
                            ->default(false)
                            ->inline(false)
                            ->helperText('⭐ The default form will be shown for final onboarding. Only ONE form can be default at a time.'),

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
                                    ->options(array_combine(
                                        FinalOnboardingFormField::FIELD_TYPES,
                                        array_map('ucfirst', FinalOnboardingFormField::FIELD_TYPES)
                                    ))
                                    ->required()
                                    ->searchable()
                                    ->label('Field Type')
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        // Auto-generate field_name from field_label for regular fields
                                        $fieldLabel = $get('field_label');
                                        if ($fieldLabel && !$get('field_name')) {
                                            $set('field_name', Str::slug($fieldLabel, '_'));
                                        }
                                    }),

                                FormFields\TextInput::make('field_label')
                                    ->required()
                                    ->label('Display Label')
                                    ->placeholder('e.g., Business Name')
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
                                    ->placeholder('e.g., business_name')
                                    ->helperText('Use lowercase, no spaces (e.g., business_name)')
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
                    ->description('Define the fields that will appear in the final onboarding form (before partnership selection)')
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

                Tables\Columns\IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('')
                    ->trueColor('warning')
                    ->tooltip(fn (FinalOnboardingForm $record): ?string =>
                        $record->is_default ? 'This is the default final onboarding form' : null
                    ),

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
            'index' => Pages\ListFinalOnboardingForms::route('/'),
            'create' => Pages\CreateFinalOnboardingForm::route('/create'),
            'view' => Pages\ViewFinalOnboardingForm::route('/{record}'),
            'edit' => Pages\EditFinalOnboardingForm::route('/{record}/edit'),
        ];
    }
}
