<?php

namespace App\Filament\Gudang\Resources\GudangAreaResource\Pages;

use App\Filament\Gudang\Resources\GudangAreaResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageGudangAreas extends ManageRecords
{
    protected static string $resource = GudangAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('+ Tambah Area Gudang')
                ->modalWidth('2xl')
                ->slideOver(),
        ];
    }
}
