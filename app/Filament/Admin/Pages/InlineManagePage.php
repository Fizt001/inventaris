<?php

namespace App\Filament\Admin\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;

/**
 * Base class untuk halaman master data dengan pola:
 *  - Form create inline di atas halaman
 *  - Tabel data di bawah (Edit & Delete pakai modal dialog)
 */
abstract class InlineManagePage extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string $view = 'filament.admin.pages.inline-manage';

    /** State form create — gunakan statePath('createData') di child class */
    public array $createData = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    /** Override untuk judul section form create */
    public function getCreateHeading(): string
    {
        return 'Tambah ' . static::$title;
    }

    /** Dipanggil saat tombol "Simpan Data" diklik */
    public function create(): void
    {
        $data = $this->form->getState();
        $this->handleCreate($data);

        $this->form->fill();

        Notification::make()
            ->title('Data berhasil disimpan')
            ->success()
            ->send();
    }

    /** Reset form ke kondisi kosong */
    public function resetForm(): void
    {
        $this->form->fill();
    }

    /** Override di child class untuk proses penyimpanan */
    abstract protected function handleCreate(array $data): void;
}
