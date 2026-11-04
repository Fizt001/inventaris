<?php

namespace App\Filament\Admin\Pages;

use App\Models\MasterReference;
use App\Models\Unit;
use App\Models\UnitRoom;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;

class ManageUnits extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Unit / Lab';
    protected static ?string $title           = 'Unit & Laboratorium';
    protected static ?string $slug            = 'manage-units';
    protected static ?int    $navigationSort  = 1;

    protected static string $view = 'filament.admin.pages.manage-units';

    public function getHeading(): string
    {
        return '';
    }

    /** ID unit yang sedang aktif/dipilih di Card 1 */
    public ?int $selectedUnitId = null;

    /** Form data untuk Card Paling Atas (Create / Edit Unit) */
    public array $unitForm = [];

    /** Kata kunci pencarian unit di Card 1 */
    public string $searchUnit = '';

    /** Daftar card ruangan baru yang sedang ditambahkan di Card 2 */
    public array $newRooms = [];

    /** ID ruangan yang sedang diedit inline di Card 2 */
    public ?int $editingRoomId = null;

    /** Data ruangan yang sedang diedit inline */
    public array $editingRoomData = [];

    public function mount(): void
    {
        $this->initUnitForm();

        // Default: pilih unit pertama jika ada agar langsung menampilkan ruangannya
        $firstUnit = Unit::orderBy('urutan')->first();
        if ($firstUnit) {
            $this->selectUnit($firstUnit->id);
        }
    }

    /** Reset form unit ke default kosong (Mode Tambah Baru) */
    public function initUnitForm(): void
    {
        $this->unitForm = [
            'nama'        => '',
            'kode'        => '',
            'tipe'        => 'jurusan',
            'kepala_nama' => '',
            'kepala_nip'  => '',
            'aktif'       => true,
            'urutan'      => 0,
        ];
    }

    /**
     * Saat user mengklik unit di Card 1:
     * - Unit menjadi berwarna biru (terpilih)
     * - Card atas berubah menjadi mode edit untuk unit tersebut
     * - Card 2 memuat daftar ruangan unit tersebut
     * - Menghilangkan form card ruangan baru yang belum disimpan dari unit sebelumnya
     */
    public function selectUnit(int $id): void
    {
        $unit = Unit::find($id);
        if (! $unit) {
            return;
        }

        $this->selectedUnitId = $id;

        // Muat data unit ke form atas untuk diedit
        $this->unitForm = [
            'nama'        => $unit->nama,
            'kode'        => $unit->kode,
            'tipe'        => $unit->tipe ?? 'jurusan',
            'kepala_nama' => $unit->kepala_nama ?? '',
            'kepala_nip'  => $unit->kepala_nip ?? '',
            'aktif'       => (bool) $unit->aktif,
            'urutan'      => (int) $unit->urutan,
        ];

        // Reset antrean input ruangan baru & reset editing ruangan
        $this->newRooms        = [];
        $this->editingRoomId   = null;
        $this->editingRoomData = [];
    }

    /**
     * Melepaskan pilihan unit & mengembalikan form paling atas ke mode Tambah Unit Baru
     */
    public function startCreateUnit(): void
    {
        $this->selectedUnitId = null;
        $this->initUnitForm();
        $this->newRooms        = [];
        $this->editingRoomId   = null;
        $this->editingRoomData = [];
    }

    /**
     * Menyimpan data unit (baik Tambah Unit Baru maupun Update Unit yang sedang dipilih)
     */
    public function saveUnit(): void
    {
        $this->validate([
            'unitForm.nama' => 'required|string|max:100',
            'unitForm.kode' => 'required|string|max:10',
            'unitForm.tipe' => 'required|string',
        ], [
            'unitForm.nama.required' => 'Nama Unit / Lab wajib diisi.',
            'unitForm.kode.required' => 'Kode Unit wajib diisi.',
            'unitForm.tipe.required' => 'Tipe Unit wajib dipilih.',
        ]);

        $data = $this->unitForm;
        $data['kode'] = strtoupper(trim($data['kode']));
        $data['nama'] = trim($data['nama']);

        if ($this->selectedUnitId) {
            // Mode Edit
            $unit = Unit::findOrFail($this->selectedUnitId);
            $unit->update($data);

            Notification::make()
                ->title("Unit '{$unit->nama}' berhasil diperbarui")
                ->success()
                ->send();
        } else {
            // Mode Tambah Baru
            $unit = Unit::create($data);
            $this->selectUnit($unit->id);

            Notification::make()
                ->title("Unit baru '{$unit->nama}' berhasil ditambahkan")
                ->success()
                ->send();
        }
    }

    /**
     * Menghapus unit yang sedang aktif
     */
    public function deleteUnit(?int $id = null): void
    {
        $targetId = $id ?? $this->selectedUnitId;
        if (! $targetId) {
            return;
        }

        $unit = Unit::find($targetId);
        if ($unit) {
            $nama = $unit->nama;
            $unit->rooms()->delete();
            $unit->delete();

            $next = Unit::orderBy('urutan')->first();
            if ($next) {
                $this->selectUnit($next->id);
            } else {
                $this->startCreateUnit();
            }

            Notification::make()
                ->title("Unit '{$nama}' berhasil dihapus")
                ->success()
                ->send();
        }
    }

    /**
     * Menambahkan card input ruangan baru di Card 2
     */
    public function addNewRoomCard(): void
    {
        if (! $this->selectedUnitId) {
            Notification::make()
                ->title('Pilih Unit / Lab terlebih dahulu di sebelah kiri')
                ->warning()
                ->send();
            return;
        }

        $this->newRooms[] = [
            'temp_id'          => uniqid('room_'),
            'nama'             => '',
            'penanggung_jawab' => '',
            'kode'             => '',
            'kapasitas'        => '',
            'aktif'            => true,
        ];
    }

    /**
     * Menghapus salah satu card input ruangan baru menggunakan tanda silang (✕)
     */
    public function removeNewRoomCard(int $index): void
    {
        if (isset($this->newRooms[$index])) {
            array_splice($this->newRooms, $index, 1);
        }
    }

    /**
     * Menyimpan ruangan dari salah satu card baru
     */
    public function saveNewRoom(int $index): void
    {
        if (! $this->selectedUnitId || ! isset($this->newRooms[$index])) {
            return;
        }

        $roomData = $this->newRooms[$index];
        $nama = trim($roomData['nama'] ?? '');

        if ($nama === '') {
            Notification::make()
                ->title('Nama ruangan wajib diisi')
                ->danger()
                ->send();
            return;
        }

        $unit = Unit::find($this->selectedUnitId);
        if (! $unit) {
            return;
        }

        $unit->rooms()->create([
            'nama'             => $nama,
            'penanggung_jawab' => trim($roomData['penanggung_jawab'] ?? '') ?: null,
            'kode'             => trim($roomData['kode'] ?? '') ?: null,
            'kapasitas'        => ! empty($roomData['kapasitas']) ? (int) $roomData['kapasitas'] : null,
            'aktif'            => (bool) ($roomData['aktif'] ?? true),
            'urutan'           => $unit->rooms()->count(),
        ]);

        array_splice($this->newRooms, $index, 1);

        Notification::make()
            ->title("Ruangan '{$nama}' berhasil ditambahkan ke {$unit->nama}")
            ->success()
            ->send();
    }

    /**
     * Mulai edit inline ruangan yang sudah ada
     */
    public function editRoom(int $roomId): void
    {
        $room = UnitRoom::find($roomId);
        if (! $room) {
            return;
        }

        $this->editingRoomId = $roomId;
        $this->editingRoomData = [
            'nama'             => $room->nama,
            'penanggung_jawab' => $room->penanggung_jawab ?? '',
            'kode'             => $room->kode ?? '',
            'kapasitas'        => $room->kapasitas ?? '',
            'aktif'            => (bool) $room->aktif,
        ];
    }

    /**
     * Batal edit inline ruangan
     */
    public function cancelEditRoom(): void
    {
        $this->editingRoomId   = null;
        $this->editingRoomData = [];
    }

    /**
     * Simpan perubahan edit inline ruangan
     */
    public function updateRoom(int $roomId): void
    {
        $room = UnitRoom::find($roomId);
        if (! $room) {
            return;
        }

        $nama = trim($this->editingRoomData['nama'] ?? '');
        if ($nama === '') {
            Notification::make()
                ->title('Nama ruangan wajib diisi')
                ->danger()
                ->send();
            return;
        }

        $room->update([
            'nama'             => $nama,
            'penanggung_jawab' => trim($this->editingRoomData['penanggung_jawab'] ?? '') ?: null,
            'kode'             => trim($this->editingRoomData['kode'] ?? '') ?: null,
            'kapasitas'        => ! empty($this->editingRoomData['kapasitas']) ? (int) $this->editingRoomData['kapasitas'] : null,
            'aktif'            => (bool) ($this->editingRoomData['aktif'] ?? true),
        ]);

        $this->editingRoomId   = null;
        $this->editingRoomData = [];

        Notification::make()
            ->title("Ruangan '{$room->nama}' berhasil diperbarui")
            ->success()
            ->send();
    }

    /**
     * Hapus ruangan yang sudah ada
     */
    public function deleteRoom(int $roomId): void
    {
        $room = UnitRoom::find($roomId);
        if ($room) {
            $nama = $room->nama;
            $room->delete();

            Notification::make()
                ->title("Ruangan '{$nama}' berhasil dihapus")
                ->success()
                ->send();
        }
    }

    /**
     * Quick toggle status aktif/tidak aktif ruangan
     */
    public function toggleRoomActive(int $roomId): void
    {
        $room = UnitRoom::find($roomId);
        if ($room) {
            $room->update(['aktif' => ! $room->aktif]);

            Notification::make()
                ->title("Status '{$room->nama}' diubah menjadi " . ($room->aktif ? 'Aktif' : 'Nonaktif'))
                ->info()
                ->send();
        }
    }

    /**
     * Daftar unit terfilter untuk Card 1
     */
    public function getUnitsProperty(): Collection
    {
        $query = Unit::query()->withCount('rooms')->orderBy('urutan')->orderBy('nama');

        if (! empty(trim($this->searchUnit))) {
            $s = '%' . trim($this->searchUnit) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('nama', 'like', $s)
                  ->orWhere('kode', 'like', $s)
                  ->orWhere('kepala_nama', 'like', $s);
            });
        }

        return $query->get();
    }

    /**
     * Unit terpilih saat ini
     */
    public function getSelectedUnitProperty(): ?Unit
    {
        if (! $this->selectedUnitId) {
            return null;
        }

        return Unit::with('rooms')->find($this->selectedUnitId);
    }

    /**
     * Pilihan tipe unit dari master reference
     */
    public function getTipeOptionsProperty(): array
    {
        $opts = MasterReference::getOptions('tipe_unit');
        if (empty($opts)) {
            return [
                'jurusan' => 'Jurusan',
                'gudang'  => 'Gudang',
                'sarana'  => 'Sarana & Prasarana',
                'umum'    => 'Umum',
            ];
        }

        return $opts;
    }
}
