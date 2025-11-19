<?php

namespace App\Filament\Resources\PartnershipModelResource\Pages;

use App\Filament\Resources\PartnershipModelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPartnershipModels extends ListRecords
{
    protected static string $resource = PartnershipModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
