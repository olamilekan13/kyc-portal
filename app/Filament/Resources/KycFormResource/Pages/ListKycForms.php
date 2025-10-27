<?php

namespace App\Filament\Resources\KycFormResource\Pages;

use App\Filament\Resources\KycFormResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKycForms extends ListRecords
{
    protected static string $resource = KycFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
