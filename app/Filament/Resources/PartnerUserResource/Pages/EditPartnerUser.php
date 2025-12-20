<?php

namespace App\Filament\Resources\PartnerUserResource\Pages;

use App\Filament\Resources\PartnerUserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPartnerUser extends EditRecord
{
    protected static string $resource = PartnerUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
