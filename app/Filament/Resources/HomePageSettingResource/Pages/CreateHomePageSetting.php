<?php

namespace App\Filament\Resources\HomePageSettingResource\Pages;

use App\Filament\Resources\HomePageSettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHomePageSetting extends CreateRecord
{
    protected static string $resource = HomePageSettingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
