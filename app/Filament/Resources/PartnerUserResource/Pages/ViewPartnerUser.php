<?php

namespace App\Filament\Resources\PartnerUserResource\Pages;

use App\Filament\Resources\PartnerUserResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPartnerUser extends ViewRecord
{
    protected static string $resource = PartnerUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
