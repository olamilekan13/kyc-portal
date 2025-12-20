<?php

namespace App\Filament\Resources\PartnerUserResource\Pages;

use App\Filament\Resources\PartnerUserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPartnerUsers extends ListRecords
{
    protected static string $resource = PartnerUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
