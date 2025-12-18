<?php

namespace App\Filament\Resources\PartnerOrders\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use App\Models\PartnerUser;
use App\Models\PartnershipModel;

class PartnerOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Information')
                    ->schema([
                        TextInput::make('order_number')
                            ->label('Order Number')
                            ->disabled()
                            ->dehydrated(false),

                        Select::make('partner_user_id')
                            ->label('Partner')
                            ->relationship('partner', 'email')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('partnership_model_id')
                            ->label('Partnership Model')
                            ->relationship('partnershipModel', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('partnership_model_name')
                            ->label('Model Name (Stored)')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('duration_months')
                            ->label('Duration (Months)')
                            ->numeric()
                            ->default(12)
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Pricing')
                    ->schema([
                        TextInput::make('partnership_model_price')
                            ->label('Partnership Fee')
                            ->numeric()
                            ->prefix('₦')
                            ->disabled()
                            ->dehydrated(false),

                        Toggle::make('solar_power')
                            ->label('Solar Power Package')
                            ->default(false),

                        TextInput::make('solar_power_amount')
                            ->label('Solar Power Amount')
                            ->numeric()
                            ->prefix('₦')
                            ->default(0),

                        TextInput::make('signup_fee_amount')
                            ->label('Signup Fee')
                            ->numeric()
                            ->prefix('₦')
                            ->default(0)
                            ->helperText('Usually ₦0 for additional orders'),

                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->prefix('₦')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2),

                Section::make('Payment Information')
                    ->schema([
                        Select::make('payment_method')
                            ->options([
                                'bank_transfer' => 'Bank Transfer',
                                'paystack' => 'Paystack',
                            ]),

                        Select::make('payment_status')
                            ->options([
                                'pending' => 'Pending',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                            ])
                            ->default('pending')
                            ->required(),

                        TextInput::make('payment_reference')
                            ->label('Payment Reference'),

                        DatePicker::make('paid_at')
                            ->label('Paid At'),

                        FileUpload::make('payment_proof')
                            ->label('Payment Proof')
                            ->image()
                            ->disk('public')
                            ->directory('payment-proofs'),

                        Textarea::make('payment_notes')
                            ->label('Payment Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Order Status')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'active' => 'Active',
                                'expired' => 'Expired',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->required(),

                        DatePicker::make('start_date')
                            ->label('Start Date'),

                        DatePicker::make('end_date')
                            ->label('End Date'),
                    ])
                    ->columns(3),
            ]);
    }
}
