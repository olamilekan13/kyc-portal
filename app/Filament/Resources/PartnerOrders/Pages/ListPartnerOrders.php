<?php

namespace App\Filament\Resources\PartnerOrders\Pages;

use App\Filament\Resources\PartnerOrders\PartnerOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPartnerOrders extends ListRecords
{
    protected static string $resource = PartnerOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
