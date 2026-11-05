<x-filament-panels::page>
    <style>
        .outgoing-input::placeholder {
            color: #94a3b8 !important;
            opacity: 0.75 !important;
            font-weight: 600 !important;
            font-size: 10px !important;
            letter-spacing: -0.2px;
        }
    </style>
    <div class="space-y-4">
        {{-- ═══ TOP TOOLBAR: BULAN/TAHUN DI KIRI, TAMBAH TANGGAL KELUAR & CARI DI KANAN ═══ --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-wrap items-center justify-between gap-4 w-full">
                {{-- KIRI: Pemilih Bulan & Tahun --}}
                <div class="flex items-center gap-2 shrink-0">
                    <button
                        type="button"
                        wire:click="previousMonth"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white p-2 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 shadow-xs transition-colors cursor-pointer"
                        title="Bulan Sebelumnya"
                    >
                        <x-filament::icon icon="heroicon-m-chevron-left" class="h-4 w-4" />
                    </button>

                    <div class="flex items-center gap-1.5">
                        <select
                            wire:model.live="selectedMonth"
                            class="rounded-lg border-gray-300 py-1.5 text-xs font-bold text-gray-900 shadow-xs focus:border-rose-500 focus:ring-rose-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}">
                                    {{ \Carbon\Carbon::createFromDate(2000, $m, 1)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>

                        <select
                            wire:model.live="selectedYear"
                            class="rounded-lg border-gray-300 py-1.5 text-xs font-bold text-gray-900 shadow-xs focus:border-rose-500 focus:ring-rose-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                            @for ($y = now()->year - 2; $y <= now()->year + 2; $y++)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <button
                        type="button"
                        wire:click="nextMonth"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white p-2 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 shadow-xs transition-colors cursor-pointer"
                        title="Bulan Selanjutnya"
                    >
                        <x-filament::icon icon="heroicon-m-chevron-right" class="h-4 w-4" />
                    </button>
                </div>

                {{-- KANAN: Tambah Tanggal Keluar & Cari Barang (Diberikan Jarak Proporsional) --}}
                <div class="flex flex-wrap items-center gap-4 ml-auto shrink-0" style="gap: 1.25rem;">
                    <x-filament::button
                        color="danger"
                        icon="heroicon-m-plus"
                        x-on:click="$dispatch('open-modal', { id: 'add-date-modal' })"
                    >
                        Tambah Tanggal Keluar
                    </x-filament::button>

                    {{-- Tombol Aksi Massal (Saat Ada Barang Diceklis) --}}
                    @if (! empty($selectedItemIds))
                        <div class="flex items-center gap-2 rounded-lg bg-rose-600 px-3 py-1 text-xs font-bold text-white shadow-xs">
                            <span>✓ {{ count($selectedItemIds) }} dipilih</span>
                            <button
                                type="button"
                                wire:click="openBulkKeperluanModal"
                                class="rounded bg-white px-2.5 py-0.5 text-xs font-black text-rose-700 hover:bg-rose-50 shadow-2xs cursor-pointer"
                            >
                                ⚡ Atur Keperluan Massal
                            </button>
                            <button
                                type="button"
                                wire:click="$set('selectedItemIds', [])"
                                class="text-rose-200 hover:text-white cursor-pointer px-1"
                                title="Batal Pilih"
                            >
                                ✕
                            </button>
                        </div>
                    @endif

                    <div class="relative min-w-[240px]">
                        <x-filament::icon icon="heroicon-m-magnifying-glass" class="absolute left-2.5 top-2.5 h-4 w-4 text-gray-400" />
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="searchTerm"
                            placeholder="Cari kode atau nama barang..."
                            class="w-full rounded-lg border-gray-300 py-1.5 pl-8 pr-3 text-xs shadow-xs focus:border-rose-500 focus:ring-rose-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        />
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ BARISAN TAB KATEGORI DI ATAS TABEL (PILIH KATEGORI) ═══ --}}
        @php
            $categories = $this->availableCategories;
        @endphp
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-thin">
            {{-- Tabs Masing-Masing Kategori --}}
            @foreach ($categories as $category)
                @php
                    $isActive = $selectedCategoryId === $category->id;
                @endphp
                <button
                    type="button"
                    wire:click="setCategory({{ $category->id }})"
                    style="{{ $isActive ? 'background-color: #e11d48 !important; color: #ffffff !important; border-color: #e11d48 !important;' : 'background-color: #ffffff; color: #1e293b; border-color: #cbd5e1;' }}"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all duration-200 cursor-pointer whitespace-nowrap border shadow-xs hover:opacity-90"
                >
                    <x-filament::icon icon="heroicon-m-tag" class="w-3.5 h-3.5" style="{{ $isActive ? 'color: #ffffff !important;' : 'color: #64748b;' }}" />
                    <span style="{{ $isActive ? 'color: #ffffff !important;' : 'color: #1e293b;' }}">{{ $category->nama }}</span>
                    <span
                        style="{{ $isActive ? 'background-color: rgba(255, 255, 255, 0.25) !important; color: #ffffff !important;' : 'background-color: #f1f5f9; color: #334155;' }}"
                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold"
                    >
                        {{ $category->items_count }}
                    </span>
                </button>
            @endforeach
        </div>

        {{-- ═══ TABEL DINAMIS BARANG KELUAR ═══ --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-xs">
                    {{-- ═══ THEAD ═══ --}}
                    <thead class="bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100 border-b-2 border-gray-300 dark:border-gray-700">
                        <tr>
                            {{-- 0. Checkbox Select All --}}
                            <th scope="col" class="w-[38px] min-w-[38px] px-2 py-2.5 text-center border-r border-gray-300 dark:border-gray-700">
                                <input
                                    type="checkbox"
                                    wire:model.live="selectAll"
                                    class="rounded border-gray-300 text-rose-600 shadow-xs focus:ring-rose-500 cursor-pointer h-4 w-4"
                                    title="Pilih / Batalkan Semua Barang"
                                />
                            </th>

                            {{-- 1. Kode --}}
                            <th scope="col" class="w-[100px] min-w-[100px] px-3 py-2.5 font-extrabold border-r border-gray-300 dark:border-gray-700 uppercase tracking-wider text-[11px]">
                                Kode
                            </th>

                            {{-- 2. Nama Barang / Komponen (Normal seperti biasa sama persis dengan Barang Masuk) --}}
                            <th scope="col" class="min-w-[380px] lg:min-w-[460px] px-4 py-2.5 font-extrabold border-r border-gray-300 dark:border-gray-700 uppercase tracking-wider text-[11px]">
                                Nama Barang / Komponen
                            </th>

                            {{-- 3. KOLOM TANGGAL KELUAR DINAMIS (DIRAMPINGKAN & SERAGAM DENGAN BARANG MASUK) --}}
                            @forelse ($activeDates as $date)
                                @php
                                    $carbonDate = \Carbon\Carbon::parse($date);
                                    $isToday = $date === now()->toDateString();
                                @endphp
                                <th
                                    scope="col"
                                    class="w-[58px] min-w-[58px] max-w-[62px] px-0.5 py-1 text-center border-r border-gray-300 dark:border-gray-700"
                                    style="{{ $isToday ? 'background-color: #9f1239 !important; color: #ffffff !important;' : 'background-color: #f1f5f9 !important; color: #0f172a !important;' }}"
                                >
                                    <div class="flex items-center justify-center relative py-0.5">
                                        <div class="text-center">
                                            {{-- Angka Tanggal '03', '15', '20', '22' --}}
                                            <div style="{{ $isToday ? 'color: #ffffff !important;' : 'color: #0f172a !important;' }} font-size: 13px; font-weight: 900; line-height: 1;">
                                                {{ $carbonDate->format('d') }}
                                            </div>
                                            @if ($isToday)
                                                <div style="margin-top: 1px;">
                                                    <span style="background-color: #f59e0b !important; color: #000000 !important; font-weight: 900; font-size: 7px; padding: 0.5px 2px; border-radius: 2px; display: inline-block; line-height: 1;">
                                                        HARI INI
                                                    </span>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Tombol Hapus (X) Posisi Pojok Kanan Atas --}}
                                        <button
                                            type="button"
                                            wire:click="confirmDeleteDate('{{ $date }}')"
                                            style="position: absolute; right: -2px; top: -3px; cursor: pointer; padding: 1px;"
                                            class="{{ $isToday ? 'text-rose-200 hover:text-white hover:bg-red-600' : 'text-gray-400 hover:text-red-600 hover:bg-gray-300' }} rounded-full transition-colors"
                                            title="Kelola / Hapus tanggal {{ $carbonDate->format('d') }}"
                                        >
                                            <svg class="h-2.5 w-2.5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </th>
                            @empty
                                <th scope="col" class="px-6 py-4 text-center text-gray-500 border-r border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                                    <div class="flex items-center justify-center gap-2">
                                        <span>Belum ada kolom tanggal keluar dibuka.</span>
                                        <button
                                            type="button"
                                            x-on:click="$dispatch('open-modal', { id: 'add-date-modal' })"
                                            class="font-bold text-rose-600 underline hover:text-rose-800 dark:text-rose-400 cursor-pointer"
                                        >
                                            + Buka Tanggal Sekarang
                                        </button>
                                    </div>
                                </th>
                            @endforelse

                            {{-- ═══ SATU KOTAK TERPADU: KELUAR BULAN INI, SISA, DAN AKSI (TETAP MEMANJANG HORIZONTAL SEPERTI BIASA) ═══ --}}
                            <th
                                scope="col"
                                style="width: 270px; min-width: 270px; max-width: 270px;"
                                class="p-0 text-center border-l-2 border-slate-300 dark:border-gray-600 bg-slate-100 dark:bg-gray-800"
                            >
                                <div style="width: 270px; min-width: 270px; display: flex; flex-direction: row; align-items: center;" class="divide-x divide-slate-300 dark:divide-gray-700 py-2 text-[10px] font-extrabold uppercase tracking-wider">
                                    <div style="width: 100px; min-width: 100px; flex-shrink: 0;" class="px-1 text-center text-rose-950 dark:text-rose-200 leading-tight">
                                        Keluar Bln Ini
                                    </div>
                                    <div style="width: 85px; min-width: 85px; flex-shrink: 0;" class="px-1 text-center text-emerald-950 dark:text-emerald-200 leading-tight">
                                        Sisa
                                    </div>
                                    <div style="width: 85px; min-width: 85px; flex-shrink: 0;" class="px-1 text-center text-gray-700 dark:text-gray-300 leading-tight">
                                        Aksi
                                    </div>
                                </div>
                            </th>
                        </tr>
                    </thead>

                    {{-- ═══ TBODY: MENAMPILKAN BARANG SECARA LANGSUNG (DIFILTER LEWAT TAB KATEGORI) ═══ --}}
                    @php
                        $matrixData    = $this->matrixData;
                        $runningStocks = $this->runningStockData;
                        $itemTotals    = $this->itemMonthTotals;
                        $itemSisa      = $this->itemRemainingStocks;
                        $itemsList     = $this->matrixItems;
                    @endphp

                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                        @forelse ($itemsList as $item)
                            @php
                                $totalKeluarBulanIni = $itemTotals[$item->id] ?? 0;
                                $sisaStok = $itemSisa[$item->id] ?? 0;
                            @endphp
                            <tr wire:key="outgoing-item-row-{{ $item->id }}" class="hover:bg-slate-50 dark:hover:bg-gray-800/50 transition-colors">
                                {{-- 0. Checkbox per Barang --}}
                                <td class="w-[38px] min-w-[38px] px-2 py-2 text-center border-r border-gray-200 dark:border-gray-800">
                                    <input
                                        type="checkbox"
                                        value="{{ $item->id }}"
                                        wire:model.live="selectedItemIds"
                                        class="rounded border-gray-300 text-rose-600 shadow-xs focus:ring-rose-500 cursor-pointer h-4 w-4"
                                    />
                                </td>

                                {{-- 1. Kode --}}
                                <td class="w-[100px] min-w-[100px] px-3 py-2 font-mono text-xs font-bold text-rose-600 dark:text-rose-400 border-r border-gray-200 dark:border-gray-800 whitespace-nowrap">
                                    <span class="rounded-md bg-rose-50 px-2 py-0.5 dark:bg-rose-950/60 border border-rose-100 dark:border-rose-900/50">
                                        {{ $item->kode }}
                                    </span>
                                </td>

                                {{-- 2. Nama Barang / Komponen --}}
                                <td class="min-w-[380px] lg:min-w-[460px] px-4 py-2 font-bold text-gray-950 dark:text-white border-r border-gray-200 dark:border-gray-800 text-[13px]">
                                    {{ $item->nama }}
                                </td>

                                {{-- 3. KOLOM TANGGAL KELUAR DINAMIS (DIRAMPINGKAN & STOK DIJADIKAN PLACEHOLDER INPUT) --}}
                                @forelse ($activeDates as $date)
                                    @php
                                        $val = $matrixData[$item->id][$date] ?? null;
                                        $stokTersedia = (int) ($runningStocks[$item->id][$date] ?? 0);
                                        $isToday = $date === now()->toDateString();
                                        $maxLimit = $stokTersedia;
                                        $isZeroStock = ($stokTersedia <= 0 && empty($val));
                                    @endphp
                                    <td class="w-[58px] min-w-[58px] max-w-[62px] p-0.5 text-center border-r border-gray-200 dark:border-gray-800 {{ $isToday ? 'bg-rose-50/40 dark:bg-rose-950/20' : '' }}">
                                        {{-- Kotak Input Pengeluaran (Placeholder Abu-abu 'S = 500') --}}
                                        <input
                                            wire:key="outgoing-cell-{{ $item->id }}-{{ $date }}-{{ $val }}-{{ $stokTersedia }}"
                                            type="text"
                                            inputmode="numeric"
                                            value="{{ $val ?: '' }}"
                                            placeholder="S = {{ $stokTersedia }}"
                                            data-max="{{ $maxLimit }}"
                                            @if($isZeroStock) disabled @endif
                                            oninput="this.value = this.value.replace(/[^0-9]/g, ''); let m = parseInt(this.dataset.max) || 0; let v = parseInt(this.value); if (!isNaN(v) && v > m) { this.value = m; }"
                                            onchange="this.value = this.value.replace(/[^0-9]/g, ''); let m = parseInt(this.dataset.max) || 0; let v = parseInt(this.value); if (!isNaN(v) && v > m) { this.value = m; }"
                                            onkeydown="if(event.key === 'ArrowUp') { let m = parseInt(this.dataset.max) || 0; let v = (parseInt(this.value) || 0) + 1; if(v <= m) this.value = v; else this.value = m; event.preventDefault(); } if(event.key === 'ArrowDown') { let v = (parseInt(this.value) || 0) - 1; if(v >= 0) this.value = v; else this.value = 0; event.preventDefault(); }"
                                            onpaste="setTimeout(() => { this.value = this.value.replace(/[^0-9]/g, ''); let m = parseInt(this.dataset.max) || 0; let v = parseInt(this.value); if (!isNaN(v) && v > m) { this.value = m; } }, 10)"
                                            wire:change="updateOutgoingQty({{ $item->id }}, '{{ $date }}', $event.target.value)"
                                            wire:loading.class="opacity-40 pointer-events-none"
                                            wire:target="updateOutgoingQty({{ $item->id }}, '{{ $date }}')"
                                            style="{{ $isZeroStock ? 'background-color: #f1f5f9; border: 1px solid #e2e8f0; color: #94a3b8; cursor: not-allowed;' : ($val > 0 ? 'background-color: #fee2e2 !important; border: 1.5px solid #ef4444 !important; color: #991b1b !important; font-weight: 900;' : 'background-color: #ffffff; border: 1px solid #cbd5e1; color: #0f172a; font-weight: 700;') }}"
                                            class="outgoing-input w-full h-7 text-center text-xs rounded transition-all focus:ring-1 focus:ring-rose-500 focus:outline-hidden p-0 font-bold"
                                            title="{{ $isZeroStock ? 'Stok kosong (0), tidak dapat melakukan pengeluaran' : 'Stok tersedia: ' . $stokTersedia . ' ' . $item->satuan . ' (Maksimal: ' . $maxLimit . ')' }}"
                                        />
                                    </td>
                                @empty
                                    <td class="px-4 py-2 text-center text-gray-400 border-r border-gray-200 dark:border-gray-800">
                                        -
                                    </td>
                                @endforelse

                                {{-- ═══ SATU KOTAK TERPADU: KELUAR BULAN INI, SISA, DAN AKSI ═══ --}}
                                <td
                                    style="width: 270px; min-width: 270px; max-width: 270px;"
                                    class="p-0 text-center border-l-2 border-slate-300 dark:border-gray-600 bg-slate-50/70 dark:bg-gray-800/40 border-b border-gray-100 dark:border-gray-800"
                                >
                                    <div style="width: 270px; min-width: 270px; display: flex; flex-direction: row; align-items: center;" class="divide-x divide-slate-200 dark:divide-gray-700 py-1.5">
                                        {{-- 1. Keluar Bulan Ini --}}
                                        <div wire:key="outgoing-keluar-box-{{ $item->id }}-{{ $totalKeluarBulanIni }}" style="width: 100px; min-width: 100px; flex-shrink: 0;" class="flex items-center justify-center px-1">
                                            <span class="inline-flex items-center justify-center min-w-[34px] rounded px-1.5 py-0.5 text-xs font-black {{ $totalKeluarBulanIni > 0 ? 'bg-rose-100 text-rose-900 dark:bg-rose-950/80 dark:text-rose-200 border border-rose-200 dark:border-rose-800' : 'text-gray-400' }}">
                                                {{ $totalKeluarBulanIni > 0 ? "-{$totalKeluarBulanIni}" : '0' }}
                                            </span>
                                        </div>

                                        {{-- 2. Sisa (Stok Fisik Lab) --}}
                                        <div wire:key="outgoing-sisa-box-{{ $item->id }}-{{ $sisaStok }}" style="width: 85px; min-width: 85px; flex-shrink: 0;" class="flex items-center justify-center px-1">
                                            <span class="inline-flex items-center justify-center gap-0.5 min-w-[36px] rounded px-1.5 py-0.5 text-xs font-black {{ $sisaStok > 0 ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-red-50 text-red-700 dark:bg-red-950/70 dark:text-red-300 border border-red-200 dark:border-red-800' }}">
                                                {{ $sisaStok }}
                                                <span class="text-[9px] font-semibold opacity-70">{{ $item->satuan }}</span>
                                            </span>
                                        </div>

                                        {{-- 3. Aksi: Tombol Detail --}}
                                        <div style="width: 85px; min-width: 85px; flex-shrink: 0;" class="flex items-center justify-center px-1">
                                            <button
                                                type="button"
                                                wire:click="openDetailModal({{ $item->id }})"
                                                class="inline-flex items-center justify-center gap-1 rounded bg-white dark:bg-gray-900 px-2.5 py-0.5 text-xs font-bold text-gray-700 hover:text-rose-700 hover:border-rose-300 dark:text-gray-300 dark:hover:text-rose-400 border border-gray-200 dark:border-gray-700 transition-all shadow-2xs cursor-pointer"
                                                title="Lihat Detail Riwayat Pengeluaran"
                                            >
                                                <x-filament::icon icon="heroicon-m-eye" class="h-3.5 w-3.5 text-rose-500" />
                                                <span>Detail</span>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ (count($activeDates) ?: 1) + 4 }}" class="py-12 text-center text-gray-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <x-filament::icon icon="heroicon-o-cube" class="h-10 w-10 text-gray-300 dark:text-gray-600" />
                                        <p class="text-sm font-medium">Tidak ada barang yang ditemukan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ═══ FOOTER STATISTIK ═══ --}}
            <div class="flex flex-wrap items-center justify-between gap-4 border-t border-gray-200 bg-gray-50/80 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/60">
                <div class="text-xs text-gray-600 dark:text-gray-400">
                    Menampilkan <strong class="text-gray-900 dark:text-white">{{ $this->matrixItems->count() }}</strong> barang dalam kategori
                    <strong class="text-rose-600 dark:text-rose-400">{{ optional($this->availableCategories->firstWhere('id', $selectedCategoryId))->nama }}</strong>
                </div>

                <div class="flex items-center gap-4 text-xs">
                    <div class="flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                        <span class="text-gray-600 dark:text-gray-400">Total Pengeluaran Bulan Ini:</span>
                        <strong class="rounded bg-rose-100 px-2 py-0.5 font-mono font-bold text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                            {{ $this->grandMonthTotal }} unit
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ MODAL KALENDER PEMILIH TANGGAL KELUAR (STANDARD 7-COLUMN MONTHLY CALENDAR) ═══ --}}
    <x-filament::modal id="add-date-modal" width="md">
        <x-slot name="heading">
            <div class="flex items-center gap-2 text-sm font-bold">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300">
                    <x-filament::icon icon="heroicon-m-calendar-days" class="h-4 w-4" />
                </span>
                <span>Pilih Tanggal Keluar ({{ $this->selectedPeriodLabel }})</span>
            </div>
        </x-slot>

        @php
            $calData = $this->calendarMonthData;
        @endphp

        <div class="py-2">
            {{-- Header Nama Hari (7 Kolom Kalender Umum) --}}
            <div class="grid grid-cols-7 gap-1 text-center mb-2">
                <div class="text-[11px] font-black text-red-500 uppercase tracking-wider py-1">Min</div>
                <div class="text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider py-1">Sen</div>
                <div class="text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider py-1">Sel</div>
                <div class="text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider py-1">Rab</div>
                <div class="text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider py-1">Kam</div>
                <div class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider py-1">Jum</div>
                <div class="text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider py-1">Sab</div>
            </div>

            {{-- Grid Tanggal Bulanan (7 Kolom) --}}
            <div class="grid grid-cols-7 gap-1.5">
                @foreach ($calData['days'] as $cell)
                    @if ($cell['type'] === 'blank')
                        <div style="height: 44px;"></div>
                    @elseif ($cell['isAdded'])
                        {{-- TANGGAL SUDAH DIBUKA (MERAH SOLID & TEGAS) --}}
                        <button
                            type="button"
                            wire:click="addDateColumn('{{ $cell['date'] }}')"
                            x-on:click="$dispatch('close-modal', { id: 'add-date-modal' })"
                            style="background-color: #e11d48 !important; border: 2px solid #be123c !important; color: #ffffff !important; border-radius: 12px; height: 44px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; font-weight: 900; box-shadow: 0 2px 4px rgba(225, 29, 72, 0.35);"
                            class="transition-all hover:opacity-90 active:scale-95"
                            title="Tanggal {{ $cell['day'] }} sudah dibuka di tabel (Klik untuk pilih lagi)"
                        >
                            <span style="font-size: 15px; line-height: 1; font-weight: 900; color: #ffffff !important;">{{ $cell['day'] }}</span>
                            <span style="font-size: 8px; line-height: 1; font-weight: 900; color: #ffe4e6 !important; margin-top: 3px; letter-spacing: 0.5px;">✓ DIBUKA</span>
                        </button>
                    @else
                        {{-- TANGGAL BELUM DIBUKA (PUTIH / MERAH UNTUK MINGGU) --}}
                        <button
                            type="button"
                            wire:click="addDateColumn('{{ $cell['date'] }}')"
                            x-on:click="$dispatch('close-modal', { id: 'add-date-modal' })"
                            style="background-color: {{ $cell['isSunday'] ? '#fff1f2' : '#ffffff' }} !important; border: 1.5px solid {{ $cell['isSunday'] ? '#fecdd3' : '#e2e8f0' }} !important; color: {{ $cell['isSunday'] ? '#e11d48' : '#0f172a' }} !important; border-radius: 12px; height: 44px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; font-weight: 800;"
                            class="transition-all hover:border-rose-500 hover:shadow-xs active:scale-95"
                            title="Klik untuk membuka tanggal {{ $cell['day'] }}"
                        >
                            <span style="font-size: 15px; line-height: 1; font-weight: 800;">{{ $cell['day'] }}</span>
                        </button>
                    @endif
                @endforeach
            </div>

            {{-- Legenda Indikator --}}
            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400">
                <div class="flex items-center gap-2">
                    <span style="display: inline-block; width: 14px; height: 14px; background-color: #e11d48; border-radius: 4px; box-shadow: 0 1px 2px rgba(225, 29, 72, 0.4);"></span>
                    <span><strong style="color: #be123c; font-weight: 900;">Warna Merah (✓ DIBUKA)</strong>: Sudah aktif di tabel</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span style="display: inline-block; width: 14px; height: 14px; background-color: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 4px;"></span>
                    <span>Kotak Putih: Klik untuk membuka tanggal</span>
                </div>
            </div>
        </div>

        <x-slot name="footerActions">
            <x-filament::button
                color="gray"
                x-on:click="$dispatch('close-modal', { id: 'add-date-modal' })"
            >
                Tutup
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    {{-- ═══ MODAL KONFIRMASI HAPUS / PINDAH TANGGAL KELUAR ═══ --}}
    @if ($showDeleteConfirmModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs">
            <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                <div class="flex items-center gap-3 border-b border-gray-200 pb-4 dark:border-gray-800">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400">
                        <x-filament::icon icon="heroicon-m-exclamation-triangle" class="h-6 w-6" />
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-gray-950 dark:text-white">
                            Kelola Tanggal {{ \Carbon\Carbon::parse($dateToDelete)->translatedFormat('d F Y') }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Pilih tindakan untuk kolom tanggal ini
                        </p>
                    </div>
                </div>

                <div class="mt-4 space-y-4">
                    {{-- Opsi 1: Pindahkan Data --}}
                    <div class="rounded-xl border border-indigo-200 bg-indigo-50/70 p-4 dark:border-indigo-900/60 dark:bg-indigo-950/40">
                        <div class="flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-indigo-600 text-white text-[10px] font-bold">
                                ➔
                            </span>
                            <h4 class="text-xs font-bold text-indigo-950 dark:text-indigo-200">
                                Pindahkan ke Tanggal Lain (Salah Pilih Tanggal)
                            </h4>
                        </div>

                        <p class="mt-1.5 text-[11px] text-indigo-900/80 dark:text-indigo-300 leading-relaxed">
                            Pindahkan seluruh catatan pengeluaran barang ini ke tanggal tujuan yang benar tanpa perlu mengetik ulang:
                        </p>

                        <div class="mt-3 flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                            <input
                                type="date"
                                wire:model="targetDateToMove"
                                class="flex-1 rounded-lg border-gray-300 py-1.5 px-3 text-xs font-bold text-gray-900 shadow-xs focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />

                            <x-filament::button
                                color="primary"
                                icon="heroicon-m-arrow-right-circle"
                                wire:click="executeMoveDate"
                            >
                                Pindahkan Data
                            </x-filament::button>
                        </div>
                    </div>

                    {{-- Opsi 2: Hapus Permanen (Kembalikan Stok) --}}
                    <div class="rounded-xl border border-red-200 bg-red-50/70 p-4 dark:border-red-900/60 dark:bg-red-950/40">
                        <div class="flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-white text-[10px] font-bold">
                                🗑
                            </span>
                            <h4 class="text-xs font-bold text-red-950 dark:text-red-200">
                                Hapus Permanen Tanggal & Data Keluar
                            </h4>
                        </div>

                        <p class="mt-1.5 text-[11px] text-red-900/80 dark:text-red-300 leading-relaxed">
                            Seluruh catatan pengeluaran pada tanggal ini akan <strong>dihapus</strong> dan stok fisik barang di lab akan <strong>dikembalikan secara otomatis</strong>.
                        </p>

                        <div class="mt-3 flex justify-end">
                            <x-filament::button
                                color="danger"
                                icon="heroicon-m-trash"
                                wire:click="executeDeleteDate"
                            >
                                Ya, Hapus Tanggal & Kembalikan Stok
                            </x-filament::button>
                        </div>
                    </div>
                </div>

                {{-- Footer Modal --}}
                <div class="mt-5 pt-3 border-t border-gray-200 dark:border-gray-800 flex justify-end">
                    <x-filament::button
                        color="gray"
                        wire:click="cancelDeleteDate"
                    >
                        Batal
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══ SLIDE-OVER DRAWER NATIVE FILAMENT (SISI KANAN - PERSIS SEPERTI DAFTARKAN BARANG) ═══ --}}
    <x-filament::modal
        id="detail-barang-modal"
        :slide-over="true"
        width="2xl"
    >
        @if ($detailItem)
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <span class="rounded bg-rose-100 px-1.5 py-0.5 font-mono text-xs font-black text-rose-700 dark:bg-rose-900/80 dark:text-rose-300">
                        {{ $detailItem->kode }}
                    </span>
                    <span class="text-base font-bold text-gray-950 dark:text-white">
                        {{ $detailItem->nama }}
                    </span>
                </div>
            </x-slot>

            <x-slot name="description">
                Kategori: {{ $detailItem->category?->nama ?? 'Tanpa Kategori' }} | Lokasi: {{ $detailItem->room?->nama ?? 'Belum Ditentukan' }} | Sisa Stok Fisik: <strong class="text-rose-600 dark:text-rose-400">{{ $detailItem->stock?->stok_tersedia ?? 0 }} {{ $detailItem->satuan }}</strong>
            </x-slot>

            <div class="space-y-5 py-2">
                {{-- Quick Action: Terapkan Satu Keterangan Keperluan ke Semua Tanggal untuk Barang Ini --}}
                <div class="rounded-xl border border-rose-200 bg-rose-50/60 p-3.5 dark:border-rose-900/60 dark:bg-rose-950/30">
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                        <div>
                            <h4 class="text-xs font-bold text-rose-950 dark:text-rose-200">
                                ⚡ Terapkan Keperluan ke Seluruh Tanggal Pengeluaran Barang Ini
                            </h4>
                            <p class="text-[11px] text-rose-800/80 dark:text-rose-300">
                                Ubah keterangan pemakaian item ini di bulan {{ $this->selectedPeriodLabel }} menjadi satu keperluan sekaligus.
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <select
                                wire:model="detailQuickKeperluan"
                                class="rounded-lg border-gray-300 py-1.5 px-2.5 text-xs font-bold text-gray-900 shadow-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >
                                @foreach (\App\Filament\Kepalab\Pages\BarangKeluarPage::KEPERLUAN_OPTIONS as $optKey => $optLabel)
                                    <option value="{{ $optKey }}">{{ $optLabel }}</option>
                                @endforeach
                            </select>

                            <button
                                type="button"
                                wire:click="applyKeperluanToAllDetailTransactions($wire.detailQuickKeperluan)"
                                class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-rose-700 shadow-xs cursor-pointer whitespace-nowrap"
                            >
                                Terapkan
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Tabel Riwayat Tanggal & Pengeluaran --}}
                <div>
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-2">
                        Riwayat Pengeluaran (Bulan {{ $this->selectedPeriodLabel }})
                    </h4>

                    @if (empty($detailTransactions))
                        <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-xs text-gray-500 dark:border-gray-700">
                            Belum ada catatan pengeluaran barang pada bulan ini. Silakan isi angka pada kolom tanggal keluar di tabel.
                        </div>
                    @else
                        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800 shadow-2xs">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold border-b border-gray-200 dark:border-gray-800">
                                    <tr>
                                        <th class="px-3 py-2.5">Tanggal Keluar</th>
                                        <th class="px-3 py-2.5 text-center">Jumlah Keluar</th>
                                        <th class="px-3 py-2.5">Keterangan / Keperluan Pemakaian</th>
                                        <th class="px-3 py-2.5 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach ($detailTransactions as $dtx)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-gray-800/40" x-data="{ editing: false, catatan: '{{ $dtx['catatan'] }}' }">
                                            <td class="px-3 py-2.5 font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                                {{ $dtx['tgl_label'] }}
                                            </td>
                                            <td class="px-3 py-2.5 text-center">
                                                <span class="inline-flex rounded-md bg-rose-100 px-2 py-0.5 font-black text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                                    -{{ $dtx['qty'] }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2.5">
                                                <template x-if="!editing">
                                                    <div class="flex items-center">
                                                        <span class="rounded-lg bg-rose-50 px-2.5 py-1 font-semibold text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                                            {{ $dtx['catatan'] }}
                                                        </span>
                                                    </div>
                                                </template>
                                                <template x-if="editing">
                                                    <div class="flex items-center gap-1.5">
                                                        <select
                                                            x-model="catatan"
                                                            class="rounded border-gray-300 py-1 px-2 text-xs font-semibold dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                        >
                                                            @foreach (\App\Filament\Kepalab\Pages\BarangKeluarPage::KEPERLUAN_OPTIONS as $optKey => $optLabel)
                                                                <option value="{{ $optKey }}">{{ $optLabel }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button
                                                            type="button"
                                                            @click="$wire.updateDetailTransaction({{ $dtx['id'] }}, catatan); editing = false"
                                                            class="rounded bg-emerald-600 px-2.5 py-1 text-[11px] font-bold text-white hover:bg-emerald-700 cursor-pointer"
                                                            title="Simpan"
                                                        >
                                                            ✓ Simpan
                                                        </button>
                                                        <button
                                                            type="button"
                                                            @click="editing = false"
                                                            class="text-gray-400 hover:text-gray-600 text-xs px-1 cursor-pointer"
                                                        >
                                                            ✕
                                                        </button>
                                                    </div>
                                                </template>
                                            </td>
                                            <td class="px-3 py-2.5 text-center">
                                                <button
                                                    type="button"
                                                    @click="editing = !editing"
                                                    class="text-gray-500 hover:text-rose-600 dark:text-gray-400 dark:hover:text-rose-400 text-xs font-semibold underline cursor-pointer"
                                                >
                                                    <span x-show="!editing">Edit</span>
                                                    <span x-show="editing">Batal</span>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <x-slot name="footerActions">
                <x-filament::button
                    color="gray"
                    x-on:click="$dispatch('close-modal', { id: 'detail-barang-modal' })"
                    wire:click="closeDetailModal"
                >
                    Tutup
                </x-filament::button>
            </x-slot>
        @endif
    </x-filament::modal>

    {{-- ═══ MODAL ATUR KEPERLUAN MASSAL (POLA DAFTARKAN BARANG) ═══ --}}
    @if ($showBulkKeperluanModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs">
            <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800 animate-in fade-in zoom-in-95 duration-150">
                {{-- Header --}}
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800 bg-rose-50/60 dark:bg-rose-950/40 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-600 text-white shadow-xs">
                            <x-filament::icon icon="heroicon-m-squares-plus" class="h-5 w-5" />
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-gray-950 dark:text-white">
                                ⚡ Atur Keperluan Pengeluaran Massal
                            </h3>
                            <p class="text-[11px] text-rose-700 dark:text-rose-300 font-bold">
                                {{ count($selectedItemIds) }} barang telah diceklis
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        wire:click="closeBulkKeperluanModal"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer"
                    >
                        ✕
                    </button>
                </div>

                {{-- Form Body --}}
                <div class="p-6 space-y-4">
                    <div class="rounded-xl border border-rose-100 bg-rose-50/50 p-3 text-xs text-rose-950 dark:border-rose-900/40 dark:bg-rose-950/20 dark:text-rose-200 leading-relaxed">
                        Tentukan keperluan/tujuan pengeluaran sekaligus untuk <strong>{{ count($selectedItemIds) }} barang</strong> yang Anda pilih.
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-900 dark:text-white mb-1.5">
                            Pilih Keperluan Pengeluaran:
                        </label>
                        <select
                            wire:model="bulkKeperluan"
                            class="w-full rounded-lg border-gray-300 py-2 px-3 text-xs font-bold text-gray-900 shadow-xs focus:border-rose-500 focus:ring-rose-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                            @foreach (\App\Filament\Kepalab\Pages\BarangKeluarPage::KEPERLUAN_OPTIONS as $optKey => $optLabel)
                                <option value="{{ $optKey }}">{{ $optLabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-900 dark:text-white mb-1.5">
                            Atau Tulis Keperluan Kustom:
                        </label>
                        <input
                            type="text"
                            wire:model="bulkCustomKeperluan"
                            placeholder="Contoh: Praktik Kejuruan Elektronika Dasar Kelas XI"
                            class="w-full rounded-lg border-gray-300 py-2 px-3 text-xs shadow-xs focus:border-rose-500 focus:ring-rose-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-900 dark:text-white mb-1.5">
                            Terapkan Untuk Tanggal:
                        </label>
                        <select
                            wire:model="bulkTargetDate"
                            class="w-full rounded-lg border-gray-300 py-2 px-3 text-xs font-bold text-gray-900 shadow-xs focus:border-rose-500 focus:ring-rose-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="all">★ Semua Tanggal Pengeluaran di Bulan Ini ({{ $this->selectedPeriodLabel }})</option>
                            @foreach ($activeDates as $date)
                                <option value="{{ $date }}">Hanya Tanggal {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="border-t border-gray-200 px-6 py-3.5 dark:border-gray-800 flex items-center justify-between bg-gray-50 dark:bg-gray-800/40">
                    <button
                        type="button"
                        wire:click="closeBulkKeperluanModal"
                        class="text-xs font-bold text-gray-600 hover:text-gray-900 dark:text-gray-400 cursor-pointer"
                    >
                        Batal
                    </button>

                    <x-filament::button
                        color="danger"
                        icon="heroicon-m-check-badge"
                        wire:click="executeBulkKeperluan"
                    >
                        ✓ Terapkan ke {{ count($selectedItemIds) }} Barang
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
