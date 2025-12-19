<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SystemSettingResource\Pages;
use App\Models\SystemSetting;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components as FormFields;
use Filament\Resources\Resource;
use Filament\Schemas\Components;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SystemSettingResource extends Resource
{
    protected static ?string $model = SystemSetting::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'System Settings';

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
                            ->unique(ignoreRecord: true)
                            ->disabled(fn ($record) => $record !== null)
                            ->helperText('Unique identifier for this setting'),

                        FormFields\Select::make('group')
                            ->required()
                            ->options([
                                'general' => 'General',
                                'payments' => 'Payments',
                                'notifications' => 'Notifications',
                                'onboarding' => 'Onboarding',
                            ])
                            ->default('general')
                            ->label('Group'),

                        FormFields\Select::make('type')
                            ->required()
                            ->options([
                                'text' => 'Text',
                                'number' => 'Number',
                                'boolean' => 'Boolean',
                                'textarea' => 'Textarea',
                                'richtext' => 'Rich Text',
                                'json' => 'JSON',
                                'image' => 'Image',
                            ])
                            ->default('text')
                            ->reactive()
                            ->label('Type'),

                        FormFields\Textarea::make('description')
                            ->maxLength(65535)
                            ->rows(2)
                            ->label('Description')
                            ->columnSpanFull(),

                        FormFields\TextInput::make('value')
                            ->label('Value')
                            ->visible(fn ($get) => in_array($get('type'), ['text', 'number']))
                            ->numeric(fn ($get) => $get('type') === 'number')
                            ->required(fn ($get) => in_array($get('type'), ['text', 'number']))
                            ->dehydrated(fn ($get) => in_array($get('type'), ['text', 'number']))
                            ->columnSpanFull(),

                        FormFields\Toggle::make('value')
                            ->label('Enabled')
                            ->visible(fn ($get) => $get('type') === 'boolean')
                            ->onColor('success')
                            ->offColor('danger')
                            ->formatStateUsing(fn ($state) => filter_var($state, FILTER_VALIDATE_BOOLEAN))
                            ->dehydrated(fn ($get) => $get('type') === 'boolean')
                            ->dehydrateStateUsing(fn ($state) => $state ? 'true' : 'false')
                            ->columnSpanFull(),

                        FormFields\Textarea::make('value')
                            ->label('Value')
                            ->visible(fn ($get) => $get('type') === 'textarea')
                            ->rows(4)
                            ->required(fn ($get) => $get('type') === 'textarea')
                            ->dehydrated(fn ($get) => $get('type') === 'textarea')
                            ->columnSpanFull(),

                        FormFields\RichEditor::make('richtext_value')
                            ->label('Value (Rich Text)')
                            ->visible(fn ($get) => $get('type') === 'richtext')
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
                            ->placeholder('Enter rich text content here...')
                            ->afterStateHydrated(function ($component, $state) {
                                // Ensure state is always valid HTML for TipTap
                                if (is_null($state) || trim($state) === '') {
                                    $component->state('<p></p>');
                                }
                            })
                            ->dehydrated(false)
                            ->columnSpanFull(),

                        FormFields\Textarea::make('value')
                            ->label('Value (JSON)')
                            ->visible(fn ($get) => $get('type') === 'json')
                            ->rows(6)
                            ->helperText('Enter valid JSON')
                            ->required(fn ($get) => $get('type') === 'json')
                            ->dehydrated(fn ($get) => $get('type') === 'json')
                            ->columnSpanFull(),

                        FormFields\FileUpload::make('value')
                            ->label('Image')
                            ->visible(fn ($get) => $get('type') === 'image')
                            ->image()
                            ->disk('public')
                            ->directory('system-settings')
                            ->visibility('public')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                null,
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->maxSize(2048)
                            ->helperText('Upload an image file (max 2MB). Recommended formats: JPG, PNG')
                            ->required(fn ($get) => $get('type') === 'image')
                            ->dehydrated(fn ($get) => $get('type') === 'image')
                            ->formatStateUsing(function ($state) {
                                // FileUpload expects null or a string path, not empty string
                                if (empty($state)) {
                                    return null;
                                }
                                return $state;
                            })
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
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('group')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'payments' => 'success',
                        'notifications' => 'warning',
                        'onboarding' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('value')
                    ->label('Value')
                    ->visible(fn ($record) => $record && $record->type === 'boolean')
                    ->onColor('success')
                    ->offColor('danger')
                    ->getStateUsing(fn ($record) => filter_var($record->value, FILTER_VALIDATE_BOOLEAN))
                    ->updateStateUsing(function ($record, $state) {
                        $record->value = $state ? 'true' : 'false';
                        $record->save();
                    }),

                Tables\Columns\ImageColumn::make('value')
                    ->label('Image')
                    ->disk('public')
                    ->visible(fn ($record) => $record && $record->type === 'image')
                    ->size(60),

                Tables\Columns\TextColumn::make('value')
                    ->limit(50)
                    ->searchable()
                    ->wrap()
                    ->formatStateUsing(fn ($state, $record) => $record->type === 'richtext' ? strip_tags($state) : $state)
                    ->visible(fn ($record) => !$record || !in_array($record->type, ['boolean', 'image'])),

                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('group', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->options([
                        'general' => 'General',
                        'payments' => 'Payments',
                        'notifications' => 'Notifications',
                        'onboarding' => 'Onboarding',
                    ]),

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'text' => 'Text',
                        'number' => 'Number',
                        'boolean' => 'Boolean',
                        'textarea' => 'Textarea',
                        'richtext' => 'Rich Text',
                        'json' => 'JSON',
                        'image' => 'Image',
                    ]),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListSystemSettings::route('/'),
            'create' => Pages\CreateSystemSetting::route('/create'),
            'edit' => Pages\EditSystemSetting::route('/{record}/edit'),
        ];
    }
}
