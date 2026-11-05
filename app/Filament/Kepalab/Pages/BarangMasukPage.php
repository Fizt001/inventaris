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

class BarangMasukPage extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-arrow-down-tray';
    protected static ?string $navigationGroup = 'Operasional Toolman';
    protected static ?string $navigationLabel = 'Barang Masuk';
    protected static ?string $title           = 'Penerimaan Barang Masuk ke Lab';
    protected static ?string $slug            = 'barang-masuk';
    protected static ?int    $navigationSort  = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return session('lab_active_role', 'toolman') === 'toolman';
    }

    protected static string $view = 'filament.kepalab.pages.barang-masuk-page';

    // State Periode Bulan & Tahun
    public int $selectedYear;
    public int $selectedMonth;

    // Kolom Tanggal Dinamis yang Sedang Aktif Ditampilkan
    public array $activeDates = [];

    // State Modal Tambah Tanggal
    public bool $showAddDateModal = false;
    public ?string $selectedDateToAdd = null;

    // State Modal Konfirmasi Hapus / Pindah Tanggal & Data
    public bool $showDeleteConfirmModal = false;
    public ?string $dateToDelete = null;
    public ?string $targetDateToMove = null;

    // Opsi Sumber Barang Masuk Standar
    public const SUMBER_OPTIONS = [
        'Yayasan'                  => 'Yayasan',
        'Dana BOS / BOSP Reguler'  => 'Dana BOS / BOSP Reguler',
        'Dana BOS Kinerja'         => 'Dana BOS Kinerja',
        'Dana Komite / Sekolah'    => 'Dana Komite / Sekolah',
        'Hibah / Bantuan Pemda'    => 'Hibah / Bantuan Pemda',
        'Bantuan Industri / CSR'   => 'Bantuan Industri / CSR',
        'Pembelian Mandiri Lab'    => 'Pembelian Mandiri Lab',
        'Sumbangan / Lainnya'      => 'Sumbangan / Lainnya',
    ];

    // Sumber Masuk yang Sedang Aktif Saat Input
    public string $currentInputSource = 'Yayasan';

    // State Modal Detail Barang Masuk (Aksi -> Detail)
    public bool $showDetailModal = false;
    public ?int $detailItemId = null;
    public ?Item $detailItem = null;
    public array $detailTransactions = [];
    public ?string $detailQuickSource = 'Yayasan';

    // State Seleksi Massal (Pola Daftarkan Barang)
    public array $selectedItemIds = [];
    public bool $selectAll = false;
    public bool $showBulkSourceModal = false;
    public string $bulkSource = 'Yayasan';
    public ?string $bulkCustomSource = null;
    public string $bulkTargetDate = 'all'; // 'all' atau tanggal tertentu
    public ?string $bulkCatatan = null;

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
     * Memuat tanggal-tanggal yang memiliki riwayat barang masuk di bulan terpilih.
     * Jika belum ada, otomatis tambahkan tanggal hari ini (jika bulan berjalan).
     */
    public function loadActiveDates(): void
    {
        $unitId    = auth()->user()?->getEffectiveUnitId();
        $startDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $endDate   = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->endOfMonth();

        $itemIds = Item::where('unit_id', $unitId)->pluck('id')->toArray();

        $dates = Transaction::where('tipe', 'masuk')
            ->whereIn('item_id', $itemIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DISTINCT DATE(created_at) as tgl')
            ->orderBy('tgl')
            ->pluck('tgl')
            ->toArray();

        // Jika bulan sekarang dan belum ada tanggal transaksi, sertakan hari ini
        $today = now()->toDateString();
        if (empty($dates) && $this->selectedYear === (int) now()->year && $this->selectedMonth === (int) now()->month) {
            $dates[] = $today;
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
     * Daftar tanggal 1 sampai tanggal maksimal di bulan terpilih
     * Disertai info lengkap: hari, tanggal, status hari ini, dan apakah sudah ada di tabel
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
     * Tambah kolom tanggal baru ke tabel
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
                ->title("Kolom Tanggal {$formatted} Dibuka!")
                ->body('Ketik jumlah barang masuk langsung pada kolom tersebut.')
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
     * Buka modal konfirmasi hapus / pindah tanggal & seluruh data transaksinya
     */
    public function confirmDeleteDate(string $date): void
    {
        $this->dateToDelete = $date;
        $this->targetDateToMove = now()->toDateString();
        $this->showDeleteConfirmModal = true;
    }

    /**
     * Batal hapus / pindah tanggal
     */
    public function cancelDeleteDate(): void
    {
        $this->dateToDelete = null;
        $this->targetDateToMove = null;
        $this->showDeleteConfirmModal = false;
    }

    /**
     * Pindahkan seluruh data penerimaan barang masuk dari tanggal asal ke tanggal tujuan
     */
    public function executeMoveDate(): void
    {
        if (! $this->dateToDelete || ! $this->targetDateToMove) {
            return;
        }

        if ($this->dateToDelete === $this->targetDateToMove) {
            Notification::make()
                ->title('Tanggal Tujuan Harus Berbeda')
                ->body('Silakan ketik atau pilih tanggal lain yang berbeda dari tanggal asal.')
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
            $transactions = Transaction::where('tipe', 'masuk')
                ->whereIn('item_id', $itemIds)
                ->whereDate('created_at', $sourceDate)
                ->get();

            foreach ($transactions as $tx) {
                // Cek apakah di tanggal tujuan sudah ada catatan untuk barang yang sama
                $existingTargetTx = Transaction::where('tipe', 'masuk')
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

        // Tambahkan tanggal tujuan jika berada di bulan/tahun yang sedang aktif
        $targetCarbon = Carbon::parse($targetDate);
        if ($targetCarbon->year === $this->selectedYear && $targetCarbon->month === $this->selectedMonth) {
            if (! in_array($targetDate, $this->activeDates)) {
                $this->activeDates[] = $targetDate;
                sort($this->activeDates);
            }
        }

        $formattedSource = Carbon::parse($sourceDate)->translatedFormat('d M Y');
        $formattedTarget = Carbon::parse($targetDate)->translatedFormat('d M Y');

        Notification::make()
            ->title("Data Berhasil Dipindahkan ke {$formattedTarget}!")
            ->body($movedCount > 0
                ? "Sebanyak {$movedCount} catatan barang masuk dari tanggal {$formattedSource} berhasil dialihkan ke tanggal {$formattedTarget}."
                : "Kolom tanggal {$formattedSource} berhasil dialihkan ke tanggal {$formattedTarget}.")
            ->success()
            ->send();

        $this->dateToDelete = null;
        $this->targetDateToMove = null;
        $this->showDeleteConfirmModal = false;

        unset($this->matrixItems);
        unset($this->groupedMatrixItems);
        unset($this->matrixData);
        unset($this->itemMonthTotals);
    }

    /**
     * Hitung jumlah transaksi pada tanggal yang sedang dipilih untuk dikelola
     */
    public function getDateTransactionCountProperty(): int
    {
        if (! $this->dateToDelete) {
            return 0;
        }

        $unitId  = auth()->user()?->getEffectiveUnitId();
        $itemIds = Item::where('unit_id', $unitId)->pluck('id')->toArray();

        return Transaction::where('tipe', 'masuk')
            ->whereIn('item_id', $itemIds)
            ->whereDate('created_at', $this->dateToDelete)
            ->count();
    }

    /**
     * Eksekusi hapus tanggal beserta seluruh catatan barang masuk dan kembalikan stok
     */
    public function executeDeleteDate(): void
    {
        if (! $this->dateToDelete) {
            return;
        }

        $targetDate = $this->dateToDelete;
        $unitId     = auth()->user()?->getEffectiveUnitId();
        $itemIds    = Item::where('unit_id', $unitId)->pluck('id')->toArray();
        $deletedCount = 0;

        DB::transaction(function () use ($unitId, $itemIds, $targetDate, &$deletedCount) {
            $transactions = Transaction::where('tipe', 'masuk')
                ->whereIn('item_id', $itemIds)
                ->whereDate('created_at', $targetDate)
                ->get();

            foreach ($transactions as $tx) {
                $stock = Stock::where('item_id', $tx->item_id)
                    ->where('unit_id', $unitId)
                    ->first();

                if ($stock) {
                    $newStock = max(0, $stock->stok_tersedia - $tx->qty);
                    $stock->update(['stok_tersedia' => $newStock]);
                }

                $tx->delete();
                $deletedCount++;
            }
        });

        // Hapus tanggal dari kolom aktif
        $this->activeDates = array_values(array_filter($this->activeDates, fn ($d) => $d !== $targetDate));

        $formatted = Carbon::parse($targetDate)->translatedFormat('d F Y');

        Notification::make()
            ->title("Tanggal {$formatted} & Data Masuk Berhasil Dihapus")
            ->body($deletedCount > 0
                ? "Sebanyak {$deletedCount} data penerimaan telah dihapus dan stok barang telah dikurangi kembali."
                : "Kolom tanggal {$formatted} telah dihapus dari tabel.")
            ->success()
            ->send();

        $this->dateToDelete = null;
        $this->targetDateToMove = null;
        $this->showDeleteConfirmModal = false;

        unset($this->matrixItems);
        unset($this->groupedMatrixItems);
        unset($this->matrixData);
        unset($this->itemMonthTotals);
    }

    /**
     * Kompatibilitas jika dipanggil langsung
     */
    public function removeDateColumn(string $date): void
    {
        $this->confirmDeleteDate($date);
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

    /**
     * Daftar kategori untuk filter di unit ini beserta jumlah barang
     */
    public function getAvailableCategoriesProperty(): BaseCollection
    {
        $unitId = auth()->user()?->getEffectiveUnitId();
        return Category::where('unit_id', $unitId)
            ->withCount(['items' => fn ($q) => $q->where('aktif', true)])
            ->orderBy('nama')
            ->get();
    }

    /**
     * Mengelompokkan barang berdasarkan Kategori untuk tampilan Accordion (Buka-Tutup)
     */
    public function getGroupedMatrixItemsProperty()
    {
        return $this->matrixItems->groupBy(function ($item) {
            return $item->category?->nama ?? 'Lainnya / Tanpa Kategori';
        });
    }

    /**
     * Ambil transaksi masuk untuk tanggal-tanggal aktif
     */
    public function getMatrixDataProperty(): array
    {
        $startDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $endDate   = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->endOfMonth();

        $itemIds = $this->matrixItems->pluck('id')->toArray();

        if (empty($itemIds)) {
            return [];
        }

        $transactions = Transaction::where('tipe', 'masuk')
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
     * Total masuk bulan ini per barang (akumulasi dari semua tanggal dalam bulan terpilih)
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

    /**
     * Total grand masuk di bulan ini
     */
    public function getGrandMonthTotalProperty(): int
    {
        return array_sum($this->itemMonthTotals);
    }

    /**
     * Update angka barang masuk pada sel tanggal tertentu
     */
    public function updateIncomingQty(int $itemId, string $date, mixed $newQty): void
    {
        $unitId = auth()->user()?->getEffectiveUnitId();
        $userId = auth()->id();
        $item   = Item::where('unit_id', $unitId)->find($itemId);

        if (! $item) {
            return;
        }

        $newQty = max(0, (int) trim((string) $newQty));

        $existingTxs = Transaction::where('item_id', $itemId)
            ->where('tipe', 'masuk')
            ->whereDate('created_at', $date)
            ->get();

        $currentQty = (int) $existingTxs->sum('qty');
        $delta      = $newQty - $currentQty;

        if ($delta === 0) {
            return;
        }

        // Ambil stok
        $stock = Stock::firstOrCreate(
            ['item_id' => $itemId, 'unit_id' => $unitId],
            ['stok_tersedia' => 0, 'stok_dipinjam' => 0]
        );

        // Perbarui stok fisik (tidak pernah minus)
        $newStockValue = max(0, $stock->stok_tersedia + $delta);
        $stock->update(['stok_tersedia' => $newStockValue]);
        $stock->refresh();
        $formattedDate = Carbon::parse($date)->translatedFormat('d M Y');

        if ($newQty === 0) {
            // Hapus transaksi jika diset 0
            Transaction::where('item_id', $itemId)
                ->where('tipe', 'masuk')
                ->whereDate('created_at', $date)
                ->delete();

            Notification::make()
                ->title("Penerimaan Tgl {$formattedDate} Dibatalkan")
                ->body("Jumlah masuk [{$item->kode}] {$item->nama} dihapus. Stok disesuaikan -{$currentQty}.")
                ->warning()
                ->send();
        } elseif ($existingTxs->isNotEmpty()) {
            // Update transaksi yang ada (Koreksi kesalahan)
            $tx    = $existingTxs->first();
            $harga = $tx->harga_saat_itu > 0 ? $tx->harga_saat_itu : ($item->harga_satuan ?? 0);

            $tx->update([
                'qty'         => $newQty,
                'nilai_total' => $newQty * $harga,
                'catatan'     => "Koreksi Barang Masuk: {$newQty}",
            ]);

            if ($existingTxs->count() > 1) {
                Transaction::where('item_id', $itemId)
                    ->where('tipe', 'masuk')
                    ->whereDate('created_at', $date)
                    ->where('id', '!=', $tx->id)
                    ->delete();
            }

            if ($delta > 0) {
                Notification::make()
                    ->title("Stok [{$item->kode}] Ditambahkan +{$delta}")
                    ->body("Tgl {$formattedDate} disesuaikan menjadi {$newQty}.")
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title("Stok [{$item->kode}] Dikurangi " . abs($delta))
                    ->body("Tgl {$formattedDate} dikoreksi menjadi {$newQty}.")
                    ->info()
                    ->send();
            }
        } else {
            // Input transaksi baru pada tanggal ini
            $harga = $item->harga_satuan ?? 0;
            $sumber = $this->currentInputSource ?: 'Yayasan';

            Transaction::create([
                'kode_transaksi' => Transaction::generateKode('masuk'),
                'item_id'        => $itemId,
                'unit_asal_id'   => null,
                'unit_tujuan_id' => $unitId,
                'tipe'           => 'masuk',
                'sumber'         => $sumber,
                'qty'            => $newQty,
                'harga_saat_itu' => $harga,
                'nilai_total'    => $newQty * $harga,
                'ref_type'       => 'barang_masuk',
                'catatan'        => "Penerimaan Tgl {$date}: {$newQty} ({$sumber})",
                'user_id'        => $userId,
                'created_at'     => Carbon::parse($date)->setTime(12, 0, 0),
            ]);

            Notification::make()
                ->title("Masuk +{$newQty}: [{$item->kode}] {$item->nama}")
                ->body("Dicatat pada tgl {$formattedDate} (Sumber: {$sumber}).")
                ->success()
                ->send();
        }

        unset($this->matrixItems);
        unset($this->groupedMatrixItems);
        unset($this->matrixData);
        unset($this->itemMonthTotals);
    }

    /**
     * Buka modal Detail Barang Masuk (Aksi -> Detail)
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

    /**
     * Muat riwayat transaksi barang masuk untuk barang yang sedang dibuka di modal detail
     */
    public function loadDetailTransactions(): void
    {
        if (! $this->detailItemId) {
            return;
        }

        $startDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $endDate   = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->endOfMonth();

        $txs = Transaction::where('tipe', 'masuk')
            ->where('item_id', $this->detailItemId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $this->detailTransactions = $txs->map(function ($tx) {
            return [
                'id'        => $tx->id,
                'tanggal'   => Carbon::parse($tx->created_at)->toDateString(),
                'tgl_label' => Carbon::parse($tx->created_at)->translatedFormat('d F Y'),
                'qty'       => $tx->qty,
                'sumber'    => $tx->sumber ?: 'Yayasan',
                'catatan'   => $tx->catatan ?: '',
            ];
        })->toArray();
    }

    /**
     * Perbarui sumber dan catatan transaksi spesifik dari modal detail
     */
    public function updateDetailTransaction(int $txId, string $newSumber, ?string $newCatatan = null): void
    {
        $tx = Transaction::where('tipe', 'masuk')->find($txId);
        if ($tx) {
            $tx->update([
                'sumber'  => trim($newSumber),
                'catatan' => $newCatatan !== null ? trim($newCatatan) : $tx->catatan,
            ]);

            $this->loadDetailTransactions();

            $formatted = Carbon::parse($tx->created_at)->translatedFormat('d M Y');
            Notification::make()
                ->title('Sumber Berhasil Diperbarui')
                ->body("Transaksi tanggal {$formatted} diatur sumbernya: {$newSumber}.")
                ->success()
                ->send();
        }
    }

    /**
     * Terapkan sumber tertentu ke semua tanggal masuk item ini di bulan terpilih
     */
    public function applySourceToAllDetailTransactions(string $sumber): void
    {
        if (! $this->detailItemId || empty($sumber)) {
            return;
        }

        $startDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $endDate   = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->endOfMonth();

        $affected = Transaction::where('tipe', 'masuk')
            ->where('item_id', $this->detailItemId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->update(['sumber' => trim($sumber)]);

        $this->loadDetailTransactions();

        Notification::make()
            ->title('Semua Tanggal Diperbarui')
            ->body("{$affected} transaksi penerimaan untuk item ini di bulan {$this->selectedPeriodLabel} telah diubah sumbernya ke: {$sumber}.")
            ->success()
            ->send();
    }

    /**
     * Tutup modal detail barang masuk
     */
    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailItemId = null;
        $this->detailItem = null;
        $this->detailTransactions = [];
        $this->dispatch('close-modal', id: 'detail-barang-modal');
    }

    /**
     * Handle toggle pilih semua (Select All)
     */
    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selectedItemIds = $this->matrixItems->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selectedItemIds = [];
        }
    }

    /**
     * Buka modal atur sumber massal (Pola Daftarkan Barang)
     */
    public function openBulkSourceModal(): void
    {
        if (empty($this->selectedItemIds)) {
            Notification::make()
                ->title('Pilih Barang Terlebih Dahulu')
                ->body('Silakan beri tanda centang (checkbox) pada barang-barang yang ingin diatur sumbernya secara massal.')
                ->warning()
                ->send();
            return;
        }

        $this->showBulkSourceModal = true;
    }

    /**
     * Tutup modal atur sumber massal
     */
    public function closeBulkSourceModal(): void
    {
        $this->showBulkSourceModal = false;
    }

    /**
     * Eksekusi penerapan sumber massal ke semua barang yang diceklis
     */
    public function executeBulkSource(): void
    {
        if (empty($this->selectedItemIds)) {
            return;
        }

        $effectiveSource = ! empty($this->bulkCustomSource) ? trim($this->bulkCustomSource) : $this->bulkSource;
        $countItems      = count($this->selectedItemIds);
        $unitId          = auth()->user()?->getEffectiveUnitId();

        $query = Transaction::where('tipe', 'masuk')
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

        $updatedRows = $query->update([
            'sumber' => $effectiveSource,
        ]);

        $this->selectedItemIds = [];
        $this->selectAll       = false;
        $this->showBulkSourceModal = false;
        $this->bulkCustomSource = null;

        Notification::make()
            ->title("⚡ Sumber [{$effectiveSource}] Berhasil Diterapkan!")
            ->body("Sebanyak {$countItems} barang terpilih telah diatur sumbernya pada {$dateLabel} ({$updatedRows} transaksi terupdate).")
            ->success()
            ->send();
    }
}
