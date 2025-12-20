<?php

namespace App\Filament\Resources\PartnerUserResource\Pages;

use App\Filament\Resources\PartnerUserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreatePartnerUser extends CreateRecord
{
    protected static string $resource = PartnerUserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate a random password if creating manually
        $data['password'] = Hash::make(Str::random(10));
        $data['password_changed'] = false;

        return $data;
    }
}
