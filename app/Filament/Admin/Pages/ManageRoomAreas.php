<?php

namespace App\Filament\Admin\Pages;

use App\Models\MasterReference;
use App\Models\RoomArea;
use App\Models\Unit;
use App\Models\UnitRoom;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;

class ManageRoomAreas extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-squares-plus';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Ruang & Area Lab';
    protected static ?string $title           = 'Ruang & Area Laboratorium';
    protected static ?string $slug            = 'manage-room-areas';
    protected static ?int    $navigationSort  = 2;

    protected static string $view = 'filament.admin.pages.manage-room-areas';

    public function getHeading(): string
    {
        return '';
    }

    /** ID ruangan yang sedang aktif/dipilih di Card 1 */
    public ?int $selectedRoomId = null;

    /** Kata kunci pencarian ruang di Card 1 */
    public string $searchRoom = '';

    /** Filter unit di Card 1 (opsional) */
    public ?int $filterUnitId = null;

    /** Daftar card area baru yang sedang ditambahkan di Card 2 */
    public array $newAreas = [];

    /** ID area yang sedang diedit inline di Card 2 */
    public ?int $editingAreaId = null;

    /** Data area yang sedang diedit inline */
    public array $editingAreaData = [];

    public function mount(): void
    {
        // Default: pilih ruangan pertama jika ada agar langsung menampilkan area-nya
        $firstRoom = UnitRoom::orderBy('unit_id')->orderBy('urutan')->orderBy('nama')->first();
        if ($firstRoom) {
            $this->selectRoom($firstRoom->id);
        }
    }

    /**
     * Saat user mengklik ruangan di Card 1:
     * - Ruangan menjadi terpilih (biru)
     * - Card 2 memuat daftar area lab di ruangan tersebut
     */
    public function selectRoom(int $id): void
    {
        $room = UnitRoom::find($id);
        if (! $room) {
            return;
        }

        $this->selectedRoomId  = $id;
        $this->newAreas        = [];
        $this->editingAreaId   = null;
        $this->editingAreaData = [];
    }

    /**
     * Menambahkan card input area baru di Card 2
     */
    public function addNewAreaCard(): void
    {
        if (! $this->selectedRoomId) {
            Notification::make()
                ->title('Pilih Ruangan terlebih dahulu di sebelah kiri')
                ->warning()
                ->send();
            return;
        }

        $this->newAreas[] = [
            'temp_id'          => uniqid('area_'),
            'nama'             => '',
            'kode'             => '',
            'kategori'         => 'praktik',
            'penanggung_jawab' => '',
            'aktif'            => true,
        ];
    }

    /**
     * Menghapus salah satu card input area baru menggunakan tanda silang (✕)
     */
    public function removeNewAreaCard(int $index): void
    {
        if (isset($this->newAreas[$index])) {
            array_splice($this->newAreas, $index, 1);
        }
    }

    /**
     * Menyimpan area dari salah satu card baru
     */
    public function saveNewArea(int $index): void
    {
        if (! $this->selectedRoomId || ! isset($this->newAreas[$index])) {
            return;
        }

        $areaData = $this->newAreas[$index];
        $nama = trim($areaData['nama'] ?? '');

        if ($nama === '') {
            Notification::make()
                ->title('Nama area wajib diisi')
                ->danger()
                ->send();
            return;
        }

        $room = UnitRoom::find($this->selectedRoomId);
        if (! $room) {
            return;
        }

        MasterReference::firstOrCreate(
            ['kategori' => 'nama_area', 'nama' => $nama],
            ['aktif' => true, 'urutan' => 99]
        );

        $room->areas()->create([
            'nama'             => $nama,
            'kode'             => ! empty($areaData['kode']) ? strtoupper(trim($areaData['kode'])) : null,
            'kategori'         => $areaData['kategori'] ?? 'praktik',
            'penanggung_jawab' => trim($areaData['penanggung_jawab'] ?? '') ?: null,
            'aktif'            => (bool) ($areaData['aktif'] ?? true),
            'urutan'           => $room->areas()->count(),
        ]);

        array_splice($this->newAreas, $index, 1);

        Notification::make()
            ->title("Area '{$nama}' berhasil ditambahkan ke {$room->nama}")
            ->success()
            ->send();
    }

    /**
     * Mulai edit inline area yang sudah ada
     */
    public function editArea(int $areaId): void
    {
        $area = RoomArea::find($areaId);
        if (! $area) {
            return;
        }

        $this->editingAreaId = $areaId;
        $this->editingAreaData = [
            'nama'             => $area->nama,
            'kode'             => $area->kode ?? '',
            'kategori'         => $area->kategori ?? 'praktik',
            'penanggung_jawab' => $area->penanggung_jawab ?? '',
            'aktif'            => (bool) $area->aktif,
        ];
    }

    /**
     * Batal edit inline area
     */
    public function cancelEditArea(): void
    {
        $this->editingAreaId   = null;
        $this->editingAreaData = [];
    }

    /**
     * Simpan perubahan edit inline area
     */
    public function updateArea(int $areaId): void
    {
        $area = RoomArea::find($areaId);
        if (! $area) {
            return;
        }

        $nama = trim($this->editingAreaData['nama'] ?? '');
        if ($nama === '') {
            Notification::make()
                ->title('Nama area wajib diisi')
                ->danger()
                ->send();
            return;
        }

        $area->update([
            'nama'             => $nama,
            'kode'             => ! empty($this->editingAreaData['kode']) ? strtoupper(trim($this->editingAreaData['kode'])) : null,
            'kategori'         => $this->editingAreaData['kategori'] ?? 'praktik',
            'penanggung_jawab' => trim($this->editingAreaData['penanggung_jawab'] ?? '') ?: null,
            'aktif'            => (bool) ($this->editingAreaData['aktif'] ?? true),
        ]);

        $this->editingAreaId   = null;
        $this->editingAreaData = [];

        Notification::make()
            ->title("Area '{$area->nama}' berhasil diperbarui")
            ->success()
            ->send();
    }

    /**
     * Hapus area yang sudah ada
     */
    public function deleteArea(int $areaId): void
    {
        $area = RoomArea::find($areaId);
        if ($area) {
            $nama = $area->nama;
            $area->delete();

            Notification::make()
                ->title("Area '{$nama}' berhasil dihapus")
                ->success()
                ->send();
        }
    }

    /**
     * Quick toggle status aktif/tidak aktif area
     */
    public function toggleAreaActive(int $areaId): void
    {
        $area = RoomArea::find($areaId);
        if ($area) {
            $area->update(['aktif' => ! $area->aktif]);

            Notification::make()
                ->title("Status '{$area->nama}' diubah menjadi " . ($area->aktif ? 'Aktif' : 'Nonaktif'))
                ->info()
                ->send();
        }
    }

    /**
     * Daftar ruangan terfilter untuk Card 1
     */
    public function getRoomsProperty(): Collection
    {
        $query = UnitRoom::query()
            ->with(['unit'])
            ->withCount('areas')
            ->orderBy('unit_id')
            ->orderBy('urutan')
            ->orderBy('nama');

        if (! empty(trim($this->searchRoom))) {
            $s = '%' . trim($this->searchRoom) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('nama', 'like', $s)
                  ->orWhere('kode', 'like', $s)
                  ->orWhere('penanggung_jawab', 'like', $s)
                  ->orWhereHas('unit', function ($uq) use ($s) {
                      $uq->where('nama', 'like', $s)->orWhere('kode', 'like', $s);
                  });
            });
        }

        if ($this->filterUnitId) {
            $query->where('unit_id', $this->filterUnitId);
        }

        return $query->get();
    }

    /**
     * Ruangan terpilih saat ini
     */
    public function getSelectedRoomProperty(): ?UnitRoom
    {
        if (! $this->selectedRoomId) {
            return null;
        }

        return UnitRoom::with(['unit', 'areas'])->find($this->selectedRoomId);
    }

    /**
     * Pilihan nama area dari master reference
     */
    public function getAreaNameOptionsListProperty(): array
    {
        return MasterReference::where('kategori', 'nama_area')
            ->where('aktif', true)
            ->orderBy('urutan')
            ->pluck('nama')
            ->toArray();
    }

    /**
     * Pilihan kategori area
     */
    public function getKategoriAreaOptionsProperty(): array
    {
        return RoomArea::KATEGORI_LABELS;
    }
}
