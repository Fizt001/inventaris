<?php

namespace App\Filament\Kepalab\Pages;

use App\Models\Category;
use App\Models\Item;
use App\Models\Stock;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\DB;

class BarangKeluarPage extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationGroup = 'Operasional Toolman';
    protected static ?string $navigationLabel = 'Barang Keluar';
    protected static ?string $title           = 'Pengeluaran & Pemakaian Barang Lab';
    protected static ?string $slug            = 'barang-keluar';
    protected static ?int    $navigationSort  = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return session('lab_active_role', 'toolman') === 'toolman';
    }

    protected static string $view = 'filament.kepalab.pages.barang-keluar-page';

    // State Periode Bulan & Tahun
    public int $selectedYear;
    public int $selectedMonth;

    // Kolom Tanggal Dinamis yang Sedang Aktif Ditampilkan
    public array $activeDates = [];

    // State Modal Tambah Tanggal Keluar
    public bool $showAddDateModal = false;
    public ?string $selectedDateToAdd = null;

    // State Modal Konfirmasi Hapus / Pindah Tanggal & Data
    public bool $showDeleteConfirmModal = false;
    public ?string $dateToDelete = null;
    public ?string $targetDateToMove = null;

    // Opsi Keperluan / Alasan Pengeluaran Standar
    public const KEPERLUAN_OPTIONS = [
        'Praktik Siswa / Praktikum'    => 'Praktik Siswa / Praktikum',
        'Pemakaian Guru / Pengajar'     => 'Pemakaian Guru / Pengajar',
        'Perbaikan / Maintenance Lab'   => 'Perbaikan / Maintenance Lab',
        'Proyek / Uji Kompetensi (UKK)' => 'Proyek / Uji Kompetensi (UKK)',
        'Bahan Habis Pakai (BHP)'       => 'Bahan Habis Pakai (BHP)',
        'Peminjaman Khusus / Ekskul'    => 'Peminjaman Khusus / Ekskul',
        'Lainnya / Umum'                => 'Lainnya / Umum',
    ];

    // State Modal Detail Barang Keluar (Aksi -> Detail)
    public bool $showDetailModal = false;
    public ?int $detailItemId = null;
    public ?Item $detailItem = null;
    public array $detailTransactions = [];
    public ?string $detailQuickKeperluan = 'Praktik Siswa / Praktikum';

    // State Seleksi Massal (Pola Daftarkan Barang)
    public array $selectedItemIds = [];
    public bool $selectAll = false;
    public bool $showBulkKeperluanModal = false;
    public string $bulkKeperluan = 'Praktik Siswa / Praktikum';
    public ?string $bulkCustomKeperluan = null;
    public string $bulkTargetDate = 'all'; // 'all' atau tanggal tertentu

    // Filter & Search
    public string $searchTerm = '';
    public ?int $selectedCategoryId = null;

    public function mount(): void
    {
        $this->selectedYear  = (int) now()->year;
        $this->selectedMonth = (int) now()->month;

        $unitId = auth()->user()?->getEffectiveUnitId();
        $firstCat = Category::where('unit_id', $unitId)->orderBy('nama')->first();
        if ($firstCat) {
            $this->selectedCategoryId = $firstCat->id;
        }

        $this->loadActiveDates();
    }

    /**
     * Memuat tanggal-tanggal yang memiliki riwayat barang keluar di bulan terpilih.
     * Jika belum ada, otomatis sertakan tanggal hari ini (jika bulan berjalan).
     */
    public function loadActiveDates(): void
    {
        $unitId    = auth()->user()?->getEffectiveUnitId();
        $startDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $endDate   = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->endOfMonth();

        $itemIds = Item::where('unit_id', $unitId)->pluck('id')->toArray();

        $dates = Transaction::where('tipe', 'keluar')
            ->whereIn('item_id', $itemIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DISTINCT DATE(created_at) as tgl')
            ->orderBy('tgl')
            ->pluck('tgl')
            ->toArray();

        // Jika bulan sekarang, selalu sertakan hari ini di kolom aktif agar siap diisi transaksi
        $today = now()->toDateString();
        if ($this->selectedYear === (int) now()->year && $this->selectedMonth === (int) now()->month) {
            if (! in_array($today, $dates)) {
                $dates[] = $today;
                sort($dates);
            }
        }

        $this->activeDates = $dates;
        $this->selectedDateToAdd = $today;
    }

    public function openAddDateModal(): void
    {
        $this->showAddDateModal = true;
    }

    public function closeAddDateModal(): void
    {
        $this->showAddDateModal = false;
    }

    public function previousMonth(): void
    {
        $date = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->subMonth();
        $this->selectedYear  = (int) $date->year;
        $this->selectedMonth = (int) $date->month;
        $this->loadActiveDates();
    }

    public function nextMonth(): void
    {
        $date = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->addMonth();
        $this->selectedYear  = (int) $date->year;
        $this->selectedMonth = (int) $date->month;
        $this->loadActiveDates();
    }

    public function updatedSelectedYear(): void
    {
        $this->loadActiveDates();
    }

    public function updatedSelectedMonth(): void
    {
        $this->loadActiveDates();
    }

    public function getSelectedPeriodLabelProperty(): string
    {
        return Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->translatedFormat('F Y');
    }

    /**
     * Daftar tanggal 1 sampai akhir bulan terpilih
     */
    public function getAvailableDaysInMonthProperty(): array
    {
        $startDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $daysCount = $startDate->daysInMonth;
        $today     = now()->toDateString();
        $days      = [];

        for ($d = 1; $d <= $daysCount; $d++) {
            $date       = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, $d);
            $dateString = $date->toDateString();

            $days[] = [
                'day'        => $d,
                'date'       => $dateString,
                'dayName'    => $date->translatedFormat('l'),
                'shortName'  => $date->translatedFormat('D'),
                'fullLabel'  => "Tanggal {$d} - " . $date->translatedFormat('l, d F Y') . ($dateString === $today ? ' ★ (HARI INI)' : ''),
                'isToday'    => $dateString === $today,
                'isWeekend'  => $date->isWeekend(),
            ];
        }

        return $days;
    }

    /**
     * Struktur Kalender 7 Kolom (Min, Sen, Sel, Rab, Kam, Jum, Sab)
     * Lengkap dengan offset sel kosong sebelum tanggal 1
     */
    public function getCalendarMonthDataProperty(): array
    {
        $startDate      = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $daysInMonth    = $startDate->daysInMonth;
        $startDayOfWeek = $startDate->dayOfWeek; // 0 = Minggu, 1 = Senin, ... 6 = Sabtu

        $days = [];

        // Sel kosong (padding) sebelum tanggal 1
        for ($i = 0; $i < $startDayOfWeek; $i++) {
            $days[] = ['type' => 'blank'];
        }

        // Sel tanggal 1 sampai akhir bulan
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date       = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, $d);
            $dateString = $date->toDateString();

            $days[] = [
                'type'     => 'day',
                'day'      => $d,
                'date'     => $dateString,
                'isAdded'  => in_array($dateString, $this->activeDates),
                'isSunday' => $date->dayOfWeek === 0,
            ];
        }

        return [
            'days' => $days,
        ];
    }

    /**
     * Tambah kolom tanggal keluar baru ke tabel
     */
    public function addDateColumn(?string $date = null): void
    {
        $targetDate = $date ?? $this->selectedDateToAdd;

        if (! $targetDate) {
            return;
        }

        if (! in_array($targetDate, $this->activeDates)) {
            $this->activeDates[] = $targetDate;
            sort($this->activeDates);

            $formatted = Carbon::parse($targetDate)->translatedFormat('d F Y');
            Notification::make()
                ->title("Kolom Tanggal Keluar {$formatted} Dibuka!")
                ->body('Ketik jumlah barang yang dikeluarkan pada kolom tersebut.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title("Kolom Tanggal Ini Sudah Terbuka di Tabel")
                ->info()
                ->send();
        }

        $this->showAddDateModal = false;
    }

    /**
     * Buka modal konfirmasi hapus / pindah tanggal pengeluaran
     */
    public function confirmDeleteDate(string $date): void
    {
        $this->dateToDelete = $date;
        $this->targetDateToMove = now()->toDateString();
        $this->showDeleteConfirmModal = true;
    }

    public function cancelDeleteDate(): void
    {
        $this->dateToDelete = null;
        $this->targetDateToMove = null;
        $this->showDeleteConfirmModal = false;
    }

    /**
     * Pindahkan catatan pengeluaran ke tanggal lain
     */
    public function executeMoveDate(): void
    {
        if (! $this->dateToDelete || ! $this->targetDateToMove) {
            return;
        }

        if ($this->dateToDelete === $this->targetDateToMove) {
            Notification::make()
                ->title('Tanggal Tujuan Harus Berbeda')
                ->body('Silakan pilih tanggal lain yang berbeda dari tanggal asal.')
                ->warning()
                ->send();
            return;
        }

        $sourceDate = $this->dateToDelete;
        $targetDate = $this->targetDateToMove;
        $unitId     = auth()->user()?->getEffectiveUnitId();
        $itemIds    = Item::where('unit_id', $unitId)->pluck('id')->toArray();
        $movedCount = 0;

        DB::transaction(function () use ($unitId, $itemIds, $sourceDate, $targetDate, &$movedCount) {
            $transactions = Transaction::where('tipe', 'keluar')
                ->whereIn('item_id', $itemIds)
                ->whereDate('created_at', $sourceDate)
                ->get();

            foreach ($transactions as $tx) {
                $existingTargetTx = Transaction::where('tipe', 'keluar')
                    ->where('item_id', $tx->item_id)
                    ->whereDate('created_at', $targetDate)
                    ->first();

                if ($existingTargetTx) {
                    $existingTargetTx->qty += $tx->qty;
                    $existingTargetTx->catatan = trim(($existingTargetTx->catatan ?? '') . " + Pindahan dari {$sourceDate}");
                    $existingTargetTx->save();
                    $tx->delete();
                } else {
                    $timePart = Carbon::parse($tx->created_at)->format('H:i:s');
                    $tx->created_at = Carbon::parse("{$targetDate} {$timePart}");
                    $tx->save();
                }

                $movedCount++;
            }
        });

        // Hapus tanggal asal dari kolom aktif
        $this->activeDates = array_values(array_filter($this->activeDates, fn ($d) => $d !== $sourceDate));

        // Tambahkan tanggal tujuan jika di bulan/tahun yang sedang aktif
        $targetCarbon = Carbon::parse($targetDate);
        if ($targetCarbon->year === $this->selectedYear && $targetCarbon->month === $this->selectedMonth) {
            if (! in_array($targetDate, $this->activeDates)) {
                $this->activeDates[] = $targetDate;
                sort($this->activeDates);
            }
        }

        $formattedTarget = Carbon::parse($targetDate)->translatedFormat('d M Y');

        Notification::make()
            ->title("Data Berhasil Dipindahkan ke {$formattedTarget}!")
            ->body("Sebanyak {$movedCount} catatan barang keluar telah dialihkan ke tanggal {$formattedTarget}.")
            ->success()
            ->send();

        $this->dateToDelete = null;
        $this->targetDateToMove = null;
        $this->showDeleteConfirmModal = false;

        unset($this->matrixItems);
        unset($this->groupedMatrixItems);
        unset($this->runningStockData);
        unset($this->matrixData);
        unset($this->itemMonthTotals);
    }

    /**
     * Hapus tanggal keluar dan KEMBALIKAN STOK FISIK YANG SUDAH KELUAR
     */
    public function executeDeleteDate(): void
    {
        if (! $this->dateToDelete) {
            return;
        }

        $targetDate   = $this->dateToDelete;
        $unitId       = auth()->user()?->getEffectiveUnitId();
        $itemIds      = Item::where('unit_id', $unitId)->pluck('id')->toArray();
        $deletedCount = 0;

        DB::transaction(function () use ($unitId, $itemIds, $targetDate, &$deletedCount) {
            $transactions = Transaction::where('tipe', 'keluar')
                ->whereIn('item_id', $itemIds)
                ->whereDate('created_at', $targetDate)
                ->get();

            foreach ($transactions as $tx) {
                // Pengeluaran dibatalkan -> stok barang dikembalikan ke lab!
                $stock = Stock::where('item_id', $tx->item_id)
                    ->where('unit_id', $unitId)
                    ->first();

                if ($stock) {
                    $stock->increment('stok_tersedia', $tx->qty);
                }

                $tx->delete();
                $deletedCount++;
            }
        });

        $this->activeDates = array_values(array_filter($this->activeDates, fn ($d) => $d !== $targetDate));
        $formatted = Carbon::parse($targetDate)->translatedFormat('d F Y');

        Notification::make()
            ->title("Tanggal Keluar {$formatted} Dihapus")
            ->body("Sebanyak {$deletedCount} data pengeluaran dihapus dan stok barang telah dikembalikan secara otomatis.")
            ->success()
            ->send();

        $this->dateToDelete = null;
        $this->targetDateToMove = null;
        $this->showDeleteConfirmModal = false;

        unset($this->matrixItems);
        unset($this->groupedMatrixItems);
        unset($this->runningStockData);
        unset($this->matrixData);
        unset($this->itemMonthTotals);
    }

    /**
     * Ambil daftar barang yang ditampilkan
     */
    public function getMatrixItemsProperty(): BaseCollection
    {
        $unitId = auth()->user()?->getEffectiveUnitId();

        if (empty($this->selectedCategoryId)) {
            $firstCat = Category::where('unit_id', $unitId)->orderBy('nama')->first();
            $this->selectedCategoryId = $firstCat?->id;
        }

        $query = Item::query()
            ->where('unit_id', $unitId)
            ->where('aktif', true)
            ->with(['stock', 'category']);

        if (! empty($this->searchTerm)) {
            $term = trim($this->searchTerm);
            $query->where(function ($q) use ($term) {
                $q->where('kode', 'like', "%{$term}%")
                  ->orWhere('nama', 'like', "%{$term}%");
            });
        }

        if (! empty($this->selectedCategoryId)) {
            $query->where('category_id', $this->selectedCategoryId);
        }

        return $query->orderBy('nama')->get();
    }

    public function setCategory(?int $categoryId): void
    {
        $this->selectedCategoryId = $categoryId;
    }

    public function getTotalItemsCountProperty(): int
    {
        $unitId = auth()->user()?->getEffectiveUnitId();
        return Item::where('unit_id', $unitId)->where('aktif', true)->count();
    }

    public function getAvailableCategoriesProperty(): BaseCollection
    {
        $unitId = auth()->user()?->getEffectiveUnitId();
        return Category::where('unit_id', $unitId)
            ->withCount(['items' => fn ($q) => $q->where('aktif', true)])
            ->orderBy('nama')
            ->get();
    }

    public function getGroupedMatrixItemsProperty()
    {
        return $this->matrixItems->groupBy(function ($item) {
            return $item->category?->nama ?? 'Lainnya / Tanpa Kategori';
        });
    }

    /**
     * Jumlah pengeluaran per item per tanggal di bulan ini
     */
    public function getMatrixDataProperty(): array
    {
        $startDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $endDate   = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->endOfMonth();

        $itemIds = $this->matrixItems->pluck('id')->toArray();

        if (empty($itemIds)) {
            return [];
        }

        $transactions = Transaction::where('tipe', 'keluar')
            ->whereIn('item_id', $itemIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('item_id, DATE(created_at) as tgl, SUM(qty) as total_qty')
            ->groupBy('item_id', 'tgl')
            ->get();

        $matrix = [];
        foreach ($transactions as $tx) {
            $matrix[$tx->item_id][$tx->tgl] = (int) $tx->total_qty;
        }

        return $matrix;
    }

    /**
     * ═══ LOGIKA STOK TERSEDIA PADA MASING-MASING SEL TANGGAL (S = ...) ═══
     *
     * Kapasitas pengeluaran maksimal untuk setiap sel (barang, tanggal):
     * = Stok fisik lab saat ini (stocks.stok_tersedia) + Jumlah yang sudah dikeluarkan pada sel tersebut.
     *
     * Hal ini menjamin:
     * 1. Seluruh barang yang memiliki stok lab (seperti C150 500 pcs, alat ukur, tang potong, dll)
     *    selalu memiliki stok tersedia (S = ...) di kolom pengeluaran dan tidak terkunci/0.
     * 2. Pengguna tidak dapat mengeluarkan melebihi total stok fisik lab yang ada.
     */
    public function getRunningStockDataProperty(): array
    {
        $matrixData   = $this->matrixData;
        $runningStock = [];

        foreach ($this->matrixItems as $item) {
            $currentStock = (int) ($item->stock?->stok_tersedia ?? 0);

            foreach ($this->activeDates as $date) {
                $cellQty = (int) ($matrixData[$item->id][$date] ?? 0);
                $runningStock[$item->id][$date] = max(0, $currentStock + $cellQty);
            }
        }

        return $runningStock;
    }

    /**
     * Total keluar bulan ini per barang
     */
    public function getItemMonthTotalsProperty(): array
    {
        $matrix = $this->matrixData;
        $totals = [];

        foreach ($this->matrixItems as $item) {
            $itemDays = $matrix[$item->id] ?? [];
            $totals[$item->id] = array_sum($itemDays);
        }

        return $totals;
    }

    public function getGrandMonthTotalProperty(): int
    {
        return array_sum($this->itemMonthTotals);
    }

    /**
     * Update angka barang keluar pada sel tanggal tertentu
     */
    public function updateOutgoingQty(int $itemId, string $date, mixed $newQty): void
    {
        $unitId = auth()->user()?->getEffectiveUnitId();
        $userId = auth()->id();
        $item   = Item::where('unit_id', $unitId)->find($itemId);

        if (! $item) {
            return;
        }

        $newQty = max(0, (int) trim((string) $newQty));

        // Cek stok tersedia pada tanggal ini
        $availableStock = $this->runningStockData[$itemId][$date] ?? 0;

        $existingTxs = Transaction::where('item_id', $itemId)
            ->where('tipe', 'keluar')
            ->whereDate('created_at', $date)
            ->get();

        $currentQty = (int) $existingTxs->sum('qty');

        // Validasi: Pengeluaran tidak boleh melebihi stok yang tersedia saat itu!
        if ($newQty > $availableStock) {
            $formattedDate = Carbon::parse($date)->translatedFormat('d M Y');
            Notification::make()
                ->title('⚠️ Melebihi Stok Tersedia!')
                ->body("Stok [{$item->kode}] {$item->nama} pada tgl {$formattedDate} hanya tersedia {$availableStock} unit. Pengeluaran otomatis dibatasi maksimal ke {$availableStock}.")
                ->warning()
                ->send();

            $newQty = $availableStock;
        }

        $delta = $newQty - $currentQty;

        if ($delta === 0) {
            return;
        }

        // Ambil data stok fisik
        $stock = Stock::firstOrCreate(
            ['item_id' => $itemId, 'unit_id' => $unitId],
            ['stok_tersedia' => 0, 'stok_dipinjam' => 0]
        );

        // Update saldo fisik (pengeluaran mengurangi saldo fisik)
        $newStockValue = max(0, $stock->stok_tersedia - $delta);
        $stock->update(['stok_tersedia' => $newStockValue]);
        $stock->refresh();

        $formattedDate = Carbon::parse($date)->translatedFormat('d M Y');

        if ($newQty === 0) {
            // Hapus catatan pengeluaran jika diset 0
            Transaction::where('item_id', $itemId)
                ->where('tipe', 'keluar')
                ->whereDate('created_at', $date)
                ->delete();

            Notification::make()
                ->title("Pengeluaran Tgl {$formattedDate} Dibatalkan")
                ->body("Pengeluaran [{$item->kode}] dihapus. Stok fisik dikembalikan +{$currentQty}.")
                ->info()
                ->send();
        } elseif ($existingTxs->isNotEmpty()) {
            // Update transaksi pengeluaran yang ada
            $tx    = $existingTxs->first();
            $harga = $tx->harga_saat_itu > 0 ? $tx->harga_saat_itu : ($item->harga_satuan ?? 0);

            $tx->update([
                'qty'         => $newQty,
                'nilai_total' => $newQty * $harga,
            ]);

            if ($existingTxs->count() > 1) {
                Transaction::where('item_id', $itemId)
                    ->where('tipe', 'keluar')
                    ->whereDate('created_at', $date)
                    ->where('id', '!=', $tx->id)
                    ->delete();
            }

            Notification::make()
                ->title("Pengeluaran [{$item->kode}] Diperbarui")
                ->body("Tgl {$formattedDate} disesuaikan menjadi {$newQty} unit.")
                ->success()
                ->send();
        } else {
            // Input transaksi pengeluaran baru
            $harga = $item->harga_satuan ?? 0;

            Transaction::create([
                'kode_transaksi' => Transaction::generateKode('keluar'),
                'item_id'        => $itemId,
                'unit_asal_id'   => $unitId,
                'unit_tujuan_id' => null,
                'tipe'           => 'keluar',
                'qty'            => $newQty,
                'harga_saat_itu' => $harga,
                'nilai_total'    => $newQty * $harga,
                'ref_type'       => 'barang_keluar',
                'catatan'        => 'Praktik Siswa / Praktikum',
                'user_id'        => $userId,
                'created_at'     => Carbon::parse($date)->setTime(14, 0, 0),
            ]);

            Notification::make()
                ->title("Keluar -{$newQty}: [{$item->kode}] {$item->nama}")
                ->body("Dicatat pada tgl {$formattedDate}. Sisa stok lab: {$newStockValue}.")
                ->success()
                ->send();
        }

        unset($this->matrixItems);
        unset($this->groupedMatrixItems);
        unset($this->runningStockData);
        unset($this->matrixData);
        unset($this->itemMonthTotals);
    }

    /**
     * Buka modal Detail Barang Keluar (Aksi -> Detail)
     */
    public function openDetailModal(int $itemId): void
    {
        $unitId = auth()->user()?->getEffectiveUnitId();
        $this->detailItem = Item::where('unit_id', $unitId)->with(['category', 'room', 'stock'])->find($itemId);

        if (! $this->detailItem) {
            return;
        }

        $this->detailItemId = $itemId;
        $this->loadDetailTransactions();
        $this->showDetailModal = true;
        $this->dispatch('open-modal', id: 'detail-barang-modal');
    }

    public function loadDetailTransactions(): void
    {
        if (! $this->detailItemId) {
            return;
        }

        $startDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $endDate   = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->endOfMonth();

        $txs = Transaction::where('tipe', 'keluar')
            ->where('item_id', $this->detailItemId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $this->detailTransactions = $txs->map(function ($tx) {
            $catatan = $tx->catatan ?: 'Praktik Siswa / Praktikum';
            if (preg_match('/\((.*?)\)$/', $catatan, $matches)) {
                $catatan = trim($matches[1]);
            } elseif (str_starts_with($catatan, 'Pengeluaran Tgl') || str_starts_with($catatan, 'Koreksi Barang Keluar')) {
                $catatan = 'Praktik Siswa / Praktikum';
            }

            return [
                'id'        => $tx->id,
                'tanggal'   => Carbon::parse($tx->created_at)->toDateString(),
                'tgl_label' => Carbon::parse($tx->created_at)->translatedFormat('d F Y'),
                'qty'       => $tx->qty,
                'catatan'   => $catatan,
            ];
        })->toArray();
    }

    public function updateDetailTransaction(int $txId, string $newCatatan): void
    {
        $tx = Transaction::where('tipe', 'keluar')->find($txId);
        if ($tx) {
            $tx->update(['catatan' => trim($newCatatan)]);
            $this->loadDetailTransactions();

            Notification::make()
                ->title('Keterangan Diperbarui')
                ->body('Keperluan pengeluaran berhasil diperbarui.')
                ->success()
                ->send();
        }
    }

    public function applyKeperluanToAllDetailTransactions(string $keperluan): void
    {
        if (! $this->detailItemId || empty($keperluan)) {
            return;
        }

        $startDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $endDate   = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->endOfMonth();

        $affected = Transaction::where('tipe', 'keluar')
            ->where('item_id', $this->detailItemId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->update(['catatan' => trim($keperluan)]);

        $this->loadDetailTransactions();

        Notification::make()
            ->title('Semua Pengeluaran Diperbarui')
            ->body("{$affected} catatan pengeluaran item ini telah diatur keperluannya: {$keperluan}.")
            ->success()
            ->send();
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailItemId = null;
        $this->detailItem = null;
        $this->detailTransactions = [];
        $this->dispatch('close-modal', id: 'detail-barang-modal');
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selectedItemIds = $this->matrixItems->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selectedItemIds = [];
        }
    }

    public function openBulkKeperluanModal(): void
    {
        if (empty($this->selectedItemIds)) {
            Notification::make()
                ->title('Pilih Barang Terlebih Dahulu')
                ->body('Beri tanda centang pada barang yang ingin diatur keperluannya secara massal.')
                ->warning()
                ->send();
            return;
        }

        $this->showBulkKeperluanModal = true;
    }

    public function closeBulkKeperluanModal(): void
    {
        $this->showBulkKeperluanModal = false;
    }

    public function executeBulkKeperluan(): void
    {
        if (empty($this->selectedItemIds)) {
            return;
        }

        $effectiveKeperluan = ! empty($this->bulkCustomKeperluan) ? trim($this->bulkCustomKeperluan) : $this->bulkKeperluan;
        $countItems         = count($this->selectedItemIds);

        $query = Transaction::where('tipe', 'keluar')
            ->whereIn('item_id', $this->selectedItemIds);

        if ($this->bulkTargetDate !== 'all') {
            $query->whereDate('created_at', $this->bulkTargetDate);
            $dateLabel = "tanggal " . Carbon::parse($this->bulkTargetDate)->translatedFormat('d F Y');
        } else {
            $startDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
            $endDate   = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->endOfMonth();
            $query->whereBetween('created_at', [$startDate, $endDate]);
            $dateLabel = "seluruh tanggal di bulan " . $this->selectedPeriodLabel;
        }

        $updatedRows = $query->update(['catatan' => $effectiveKeperluan]);

        $this->selectedItemIds = [];
        $this->selectAll       = false;
        $this->showBulkKeperluanModal = false;
        $this->bulkCustomKeperluan = null;

        Notification::make()
            ->title("⚡ Keperluan [{$effectiveKeperluan}] Berhasil Diterapkan!")
            ->body("Sebanyak {$countItems} barang terpilih telah diatur keperluannya pada {$dateLabel} ({$updatedRows} transaksi terupdate).")
            ->success()
            ->send();
    }
}
