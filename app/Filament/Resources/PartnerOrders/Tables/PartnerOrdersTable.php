<?php

namespace App\Filament\Resources\PartnerOrders\Tables;

use App\Mail\OrderApprovedMail;
use App\Mail\OrderRejectedMail;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class PartnerOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('partner.full_name')
                    ->label('Partner')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                TextColumn::make('partnership_model_name')
                    ->label('Partnership Model')
                    ->searchable(),

                TextColumn::make('total_amount')
                    ->label('Amount')
                    ->money('NGN')
                    ->sortable(),

                TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'expired' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    }),

                IconColumn::make('solar_power')
                    ->label('Solar')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime('M d, Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ]),

                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                Action::make('approve_payment')
                    ->label('Approve Payment')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->payment_status === 'pending' && $record->payment_method)
                    ->requiresConfirmation()
                    ->modalHeading('Approve Payment')
                    ->modalDescription('Are you sure you want to approve this payment? This will activate the order.')
                    ->action(function ($record) {
                        $record->update([
                            'payment_status' => 'completed',
                            'paid_at' => now(),
                        ]);

                        // Activate the order
                        $record->activate();

                        // Send email to partner
                        try {
                            Mail::to($record->partner->email)->send(new OrderApprovedMail($record));
                            Log::info('Order approved email sent', [
                                'order_id' => $record->id,
                                'partner_email' => $record->partner->email,
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Failed to send order approved email', [
                                'order_id' => $record->id,
                                'error' => $e->getMessage(),
                            ]);
                        }

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Payment Approved')
                            ->body('Order ' . $record->order_number . ' has been approved and activated.')
                            ->send();
                    }),

                Action::make('reject_payment')
                    ->label('Reject Payment')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->payment_status === 'pending' && $record->payment_method)
                    ->requiresConfirmation()
                    ->modalHeading('Reject Payment')
                    ->modalDescription('Are you sure you want to reject this payment? This will mark the payment as failed.')
                    ->action(function ($record) {
                        $record->update([
                            'payment_status' => 'failed',
                        ]);

                        // Send email to partner
                        try {
                            Mail::to($record->partner->email)->send(new OrderRejectedMail($record));
                            Log::info('Order rejected email sent', [
                                'order_id' => $record->id,
                                'partner_email' => $record->partner->email,
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Failed to send order rejected email', [
                                'order_id' => $record->id,
                                'error' => $e->getMessage(),
                            ]);
                        }

                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('Payment Rejected')
                            ->body('Order ' . $record->order_number . ' payment has been rejected.')
                            ->send();
                    }),

                Action::make('view_proof')
                    ->label('View Payment Proof')
                    ->icon('heroicon-o-photo')
                    ->visible(fn ($record) => $record->payment_proof)
                    ->url(fn ($record) => Storage::disk('public')->url($record->payment_proof))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
