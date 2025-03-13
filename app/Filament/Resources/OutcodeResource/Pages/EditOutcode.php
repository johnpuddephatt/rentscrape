<?php

namespace App\Filament\Resources\OutcodeResource\Pages;

use App\Filament\Resources\OutcodeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOutcode extends EditRecord
{
    protected static string $resource = OutcodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
