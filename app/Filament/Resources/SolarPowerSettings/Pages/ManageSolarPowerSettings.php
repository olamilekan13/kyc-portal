<?php

namespace App\Filament\Resources\SolarPowerSettings\Pages;

use App\Filament\Resources\SolarPowerSettings\SolarPowerSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSolarPowerSettings extends ManageRecords
{
    protected static string $resource = SolarPowerSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
