<?php

namespace App\Filament\Gudang\Resources\SupplierResource\Pages;

use App\Filament\Gudang\Resources\SupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSuppliers extends ManageRecords
{
    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Supplier Baru')
                ->icon('heroicon-o-plus')
                ->modalWidth('2xl'),
        ];
    }
}
