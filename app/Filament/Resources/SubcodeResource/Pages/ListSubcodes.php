<?php

namespace App\Filament\Resources\SubcodeResource\Pages;

use App\Filament\Resources\SubcodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubcodes extends ListRecords
{
    protected static string $resource = SubcodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
