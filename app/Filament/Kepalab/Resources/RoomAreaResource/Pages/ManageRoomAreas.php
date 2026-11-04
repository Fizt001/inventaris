<?php

namespace App\Filament\Kepalab\Resources\RoomAreaResource\Pages;

use App\Filament\Kepalab\Resources\RoomAreaResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageRoomAreas extends ManageRecords
{
    protected static string $resource = RoomAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Area Kerja Baru')
                ->icon('heroicon-o-plus')
                ->modalWidth('2xl')
                ->slideOver(),
        ];
    }
}
