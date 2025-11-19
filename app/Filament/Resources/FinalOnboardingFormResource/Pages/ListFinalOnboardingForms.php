<?php

namespace App\Filament\Resources\FinalOnboardingFormResource\Pages;

use App\Filament\Resources\FinalOnboardingFormResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFinalOnboardingForms extends ListRecords
{
    protected static string $resource = FinalOnboardingFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
