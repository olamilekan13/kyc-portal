<?php

namespace App\Filament\Resources\KycFormResource\Pages;

use App\Filament\Resources\KycFormResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewKycForm extends ViewRecord
{
    protected static string $resource = KycFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
