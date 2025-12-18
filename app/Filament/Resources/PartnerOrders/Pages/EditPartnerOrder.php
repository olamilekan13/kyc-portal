<?php

namespace App\Filament\Resources\PartnerOrders\Pages;

use App\Filament\Resources\PartnerOrders\PartnerOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPartnerOrder extends EditRecord
{
    protected static string $resource = PartnerOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
