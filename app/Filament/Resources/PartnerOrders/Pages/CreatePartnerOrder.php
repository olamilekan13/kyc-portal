<?php

namespace App\Filament\Resources\PartnerOrders\Pages;

use App\Filament\Resources\PartnerOrders\PartnerOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePartnerOrder extends CreateRecord
{
    protected static string $resource = PartnerOrderResource::class;
}
