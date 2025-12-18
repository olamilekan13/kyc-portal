<?php

namespace App\Filament\Resources\PartnerOrders;

use App\Filament\Resources\PartnerOrders\Pages\ListPartnerOrders;
use App\Filament\Resources\PartnerOrders\Pages\ViewPartnerOrder;
use App\Filament\Resources\PartnerOrders\Tables\PartnerOrdersTable;
use App\Models\PartnerOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class PartnerOrderResource extends Resource
{
    protected static ?string $model = PartnerOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Partner Orders';

    protected static ?string $modelLabel = 'Partner Order';

    protected static ?string $pluralModelLabel = 'Partner Orders';

    protected static string|UnitEnum|null $navigationGroup = 'Partners';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'order_number';

    public static function table(Table $table): Table
    {
        return PartnerOrdersTable::configure($table);
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
            'index' => ListPartnerOrders::route('/'),
            'view' => ViewPartnerOrder::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
