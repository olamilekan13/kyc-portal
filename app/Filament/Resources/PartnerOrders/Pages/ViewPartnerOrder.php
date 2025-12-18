<?php

namespace App\Filament\Resources\PartnerOrders\Pages;

use App\Filament\Resources\PartnerOrders\PartnerOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\IconEntry;

class ViewPartnerOrder extends ViewRecord
{
    protected static string $resource = PartnerOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Information')
                    ->schema([
                        TextEntry::make('order_number')
                            ->label('Order Number')
                            ->badge()
                            ->color('primary')
                            ->copyable(),
                        TextEntry::make('partner.full_name')
                            ->label('Partner Name'),
                        TextEntry::make('partner.email')
                            ->label('Partner Email'),
                        TextEntry::make('partnership_model_name')
                            ->label('Partnership Model'),
                        TextEntry::make('duration_months')
                            ->label('Duration')
                            ->suffix(' months'),
                        TextEntry::make('created_at')
                            ->label('Order Date')
                            ->dateTime('M d, Y h:i A'),
                    ])
                    ->columns(2),

                Section::make('Pricing Details')
                    ->schema([
                        TextEntry::make('partnership_model_price')
                            ->label('Partnership Fee')
                            ->money('NGN'),
                        IconEntry::make('solar_power')
                            ->label('Solar Power')
                            ->boolean(),
                        TextEntry::make('solar_power_amount')
                            ->label('Solar Power Amount')
                            ->money('NGN'),
                        TextEntry::make('signup_fee_amount')
                            ->label('Signup Fee')
                            ->money('NGN'),
                        TextEntry::make('total_amount')
                            ->label('Total Amount')
                            ->money('NGN')
                            ->weight('bold')
                            ->size('lg'),
                    ])
                    ->columns(2),

                Section::make('Payment Information')
                    ->schema([
                        TextEntry::make('payment_method')
                            ->label('Payment Method')
                            ->formatStateUsing(fn ($state) => $state ? ucfirst(str_replace('_', ' ', $state)) : 'N/A'),
                        TextEntry::make('payment_status')
                            ->label('Payment Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'completed' => 'success',
                                'failed' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('payment_reference')
                            ->label('Payment Reference')
                            ->placeholder('N/A'),
                        TextEntry::make('paid_at')
                            ->label('Paid At')
                            ->dateTime('M d, Y h:i A')
                            ->placeholder('Not paid yet'),
                        ImageEntry::make('payment_proof')
                            ->label('Payment Proof')
                            ->disk('public')
                            ->visible(fn ($record) => $record->payment_proof)
                            ->columnSpanFull(),
                        TextEntry::make('payment_notes')
                            ->label('Payment Notes')
                            ->placeholder('No notes')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Order Status')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'pending' => 'warning',
                                'expired' => 'danger',
                                'cancelled' => 'gray',
                                default => 'gray',
                            }),
                        TextEntry::make('start_date')
                            ->label('Start Date')
                            ->date('M d, Y')
                            ->placeholder('Not started'),
                        TextEntry::make('end_date')
                            ->label('End Date')
                            ->date('M d, Y')
                            ->placeholder('Not set'),
                    ])
                    ->columns(3),
            ]);
    }
}
