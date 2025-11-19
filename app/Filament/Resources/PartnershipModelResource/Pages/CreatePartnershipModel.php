<?php

namespace App\Filament\Resources\PartnershipModelResource\Pages;

use App\Filament\Resources\PartnershipModelResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePartnershipModel extends CreateRecord
{
    protected static string $resource = PartnershipModelResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
