<?php

namespace App\Filament\Resources\PartnershipModelResource\Pages;

use App\Filament\Resources\PartnershipModelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPartnershipModel extends EditRecord
{
    protected static string $resource = PartnershipModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
