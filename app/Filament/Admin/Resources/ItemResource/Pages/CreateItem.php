<?php

namespace App\Filament\Admin\Resources\ItemResource\Pages;

use App\Filament\Admin\Resources\ItemResource;
use App\Models\Stock;
use Filament\Resources\Pages\CreateRecord;

class CreateItem extends CreateRecord
{
    protected static string $resource = ItemResource::class;

    protected function afterCreate(): void
    {
        // Auto-create stock record
        Stock::firstOrCreate([
            'item_id' => $this->record->id,
            'unit_id' => $this->record->unit_id,
        ], ['stok_tersedia' => 0, 'stok_dipinjam' => 0]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
