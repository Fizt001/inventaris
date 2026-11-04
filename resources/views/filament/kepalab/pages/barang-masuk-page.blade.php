<x-filament-panels::page>
    <div class="space-y-4">
        {{-- ═══ TOP TOOLBAR: BULAN/TAHUN DI KIRI, TAMBAH & CARI DI KANAN ═══ --}}
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
                            class="rounded-lg border-gray-300 py-1.5 text-xs font-bold text-gray-900 shadow-xs focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}">
                                    {{ \Carbon\Carbon::createFromDate(2000, $m, 1)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>

                        <select
                            wire:model.live="selectedYear"
                            class="rounded-lg border-gray-300 py-1.5 text-xs font-bold text-gray-900 shadow-xs focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
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

                {{-- KANAN: Tambah Tanggal Masuk & Cari Barang --}}
                <div class="flex flex-wrap items-center gap-4 ml-auto shrink-0" style="gap: 1.25rem;">
                    <x-filament::button
                        color="primary"
                        icon="heroicon-m-plus"
                        x-on:click="$dispatch('open-modal', { id: 'add-date-modal' })"
                    >
                        Tambah Tanggal Masuk
                    </x-filament::button>



                    {{-- Tombol Aksi Massal (Saat Ada Barang Diceklis) --}}
                    @if (! empty($selectedItemIds))
                        <div class="flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-1 text-xs font-bold text-white shadow-xs">
                            <span>✓ {{ count($selectedItemIds) }} dipilih</span>
                            <button
                                type="button"
                                wire:click="openBulkSourceModal"
                                class="rounded bg-white px-2.5 py-0.5 text-xs font-black text-indigo-700 hover:bg-indigo-50 shadow-2xs cursor-pointer"
                            >
                                ⚡ Atur Sumber Massal
                            </button>
                            <button
                                type="button"
                                wire:click="$set('selectedItemIds', [])"
                                class="text-indigo-200 hover:text-white cursor-pointer px-1"
                                title="Batal Pilih"
                            >
                                ✕
                            </button>
                        </div>
                    @endif

                    <div class="relative min-w-[220px]">
                        <x-filament::icon icon="heroicon-m-magnifying-glass" class="absolute left-2.5 top-2.5 h-4 w-4 text-gray-400" />
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="searchTerm"
                            placeholder="Cari kode atau nama barang..."
                            class="w-full rounded-lg border-gray-300 py-1.5 pl-8 pr-3 text-xs shadow-xs focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        />
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ TABEL DINAMIS BARANG MASUK ═══ --}}
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
                                    class="rounded border-gray-300 text-indigo-600 shadow-xs focus:ring-indigo-500 cursor-pointer h-4 w-4"
                                    title="Pilih / Batalkan Semua Barang"
                                />
                            </th>

                            {{-- 1. Kode --}}
                            <th scope="col" class="w-[100px] min-w-[100px] px-3 py-2.5 font-extrabold border-r border-gray-300 dark:border-gray-700 uppercase tracking-wider text-[11px]">
                                Kode
                            </th>

                            {{-- 2. Nama Barang / Komponen (Diperbesar Luas) --}}
                            <th scope="col" class="min-w-[380px] lg:min-w-[460px] px-4 py-2.5 font-extrabold border-r border-gray-300 dark:border-gray-700 uppercase tracking-wider text-[11px]">
                                Nama Barang / Komponen
                            </th>

                            {{-- 3. KOLOM TANGGAL DINAMIS (DIRAMPINGKAN & KOMPAK) --}}
                            @forelse ($activeDates as $date)
                                @php
                                    $carbonDate = \Carbon\Carbon::parse($date);
                                    $isToday = $date === now()->toDateString();
                                @endphp
                                <th
                                    scope="col"
                                    class="w-[48px] min-w-[48px] max-w-[50px] px-0.5 py-1 text-center border-r border-gray-300 dark:border-gray-700"
                                    style="{{ $isToday ? 'background-color: #3730a3 !important; color: #ffffff !important;' : 'background-color: #e2e8f0 !important; color: #0f172a !important;' }}"
                                >
                                    <div class="flex items-center justify-center relative py-0.5">
                                        <div class="text-center">
                                            {{-- Cukup Angka Tanggal '01', '02', '03' --}}
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
                                            class="{{ $isToday ? 'text-indigo-200 hover:text-white hover:bg-red-600' : 'text-gray-400 hover:text-red-600 hover:bg-gray-300' }} rounded-full transition-colors"
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
                                        <span>Belum ada kolom tanggal dibuka.</span>
                                        <button
                                            type="button"
                                            x-on:click="$dispatch('open-modal', { id: 'add-date-modal' })"
                                            class="font-bold text-indigo-600 underline hover:text-indigo-800 dark:text-indigo-400 cursor-pointer"
                                        >
                                            + Buka Tanggal Sekarang
                                        </button>
                                    </div>
                                </th>
                            @endforelse

                            {{-- 4. Barang Masuk Bulan Ini (Paling Ujung) --}}
                            <th scope="col" class="w-[110px] min-w-[110px] px-2 py-2.5 text-center font-extrabold text-indigo-950 dark:text-indigo-200 bg-indigo-100/70 dark:bg-indigo-950/40 border-r border-gray-300 dark:border-gray-700 uppercase tracking-wider text-[10px]">
                                Masuk Bulan Ini
                            </th>

                            {{-- 5. Aksi (Ganti Status Menjadi Aksi) --}}
                            <th scope="col" class="w-[80px] min-w-[80px] px-2 py-2.5 text-center font-extrabold uppercase tracking-wider text-[10px]">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    {{-- ═══ TBODY: DIKELOMPOKKAN PER KATEGORI DENGAN ACCORDION BUKA-TUTUP ═══ --}}
                    @php
                        $matrixData = $this->matrixData;
                        $itemTotals = $this->itemMonthTotals;
                        $groupedItems = $this->groupedMatrixItems;
                    @endphp

                    @forelse ($groupedItems as $categoryName => $items)
                        <tbody
                            x-data="{ open: true }"
                            x-on:collapse-all.window="open = false"
                            x-on:expand-all.window="open = true"
                            class="border-b-2 border-slate-300 dark:border-gray-700 bg-white dark:bg-gray-900"
                        >
                            {{-- ═══ HEADER KATEGORI (SERAGAM DENGAN DAFTAR BARANG) ═══ --}}
                            <tr
                                @click="open = !open"
                                class="fi-ta-group-header cursor-pointer select-none border-y border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors"
                                title="Klik untuk buka / tutup kelompok {{ $categoryName }}"
                            >
                                <td colspan="{{ count($activeDates) + 5 }}" class="px-4 py-2.5">
                                    <div class="flex w-full items-center justify-between">
                                        {{-- Sisi Kiri: Nama Kategori + Panah Buka-Tutup --}}
                                        <div class="flex items-center gap-x-2">
                                            <h4 class="text-sm font-bold text-gray-950 dark:text-white">
                                                Kategori / Kelompok: {{ $categoryName }}
                                            </h4>

                                            <x-filament::icon-button
                                                color="gray"
                                                icon="heroicon-m-chevron-up"
                                                size="sm"
                                                class="transition-transform duration-200"
                                                ::class="{ '-rotate-180': !open }"
                                            />
                                        </div>

                                        {{-- Sisi Kanan: Tulisan Jumlah Barang (Pepet Kanan) --}}
                                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300">
                                            {{ count($items) }} Barang
                                        </span>
                                    </div>
                                </td>
                            </tr>

                            {{-- ═══ DAFTAR BARANG DI DALAM KATEGORI INI ═══ --}}
                            @foreach ($items as $item)
                                @php
                                    $totalBulanIni = $itemTotals[$item->id] ?? 0;
                                @endphp
                                <tr
                                    x-show="open"
                                    class="hover:bg-slate-50 dark:hover:bg-gray-800/50 transition-colors border-b border-gray-100 dark:border-gray-800"
                                >
                                    {{-- 0. Checkbox per Barang (Pola Daftarkan Barang) --}}
                                    <td class="w-[38px] min-w-[38px] px-2 py-2 text-center border-r border-gray-200 dark:border-gray-800">
                                        <input
                                            type="checkbox"
                                            value="{{ $item->id }}"
                                            wire:model.live="selectedItemIds"
                                            class="rounded border-gray-300 text-indigo-600 shadow-xs focus:ring-indigo-500 cursor-pointer h-4 w-4"
                                        />
                                    </td>

                                    {{-- 1. Kode --}}
                                    <td class="w-[100px] min-w-[100px] px-3 py-2 font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400 border-r border-gray-200 dark:border-gray-800 whitespace-nowrap">
                                        <span class="rounded-md bg-indigo-50 px-2 py-0.5 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-900/50">
                                            {{ $item->kode }}
                                        </span>
                                    </td>

                                    {{-- 2. Nama Barang / Komponen (Diperbesar Luas) --}}
                                    <td class="min-w-[380px] lg:min-w-[460px] px-4 py-2 font-bold text-gray-950 dark:text-white border-r border-gray-200 dark:border-gray-800 text-[13px]">
                                        {{ $item->nama }}
                                    </td>

                                    {{-- 3. Kolom Tanggal Dinamis (Input Angka Langsung - Ramping & Kompak) --}}
                                    @forelse ($activeDates as $date)
                                        @php
                                            $val = $matrixData[$item->id][$date] ?? null;
                                            $isToday = $date === now()->toDateString();
                                        @endphp
                                        <td class="w-[48px] min-w-[48px] max-w-[50px] p-0.5 text-center border-r border-gray-200 dark:border-gray-800 {{ $isToday ? 'bg-indigo-50/40 dark:bg-indigo-950/20' : '' }}">
                                            <input
                                                type="number"
                                                min="0"
                                                step="1"
                                                value="{{ $val ?: '' }}"
                                                placeholder="0"
                                                wire:change="updateIncomingQty({{ $item->id }}, '{{ $date }}', $event.target.value)"
                                                wire:loading.class="opacity-40 pointer-events-none"
                                                wire:target="updateIncomingQty({{ $item->id }}, '{{ $date }}')"
                                                style="{{ $val > 0 ? 'background-color: #d1fae5 !important; border: 1.5px solid #10b981 !important; color: #064e3b !important; font-weight: 900;' : 'background-color: #ffffff; border: 1px solid #cbd5e1; color: #0f172a; font-weight: 700;' }}"
                                                class="w-full h-7 text-center text-xs rounded transition-all focus:ring-1 focus:ring-indigo-500 focus:outline-hidden p-0"
                                            />
                                        </td>
                                    @empty
                                        <td class="px-4 py-2 text-center text-gray-400 border-r border-gray-200 dark:border-gray-800">
                                            -
                                        </td>
                                    @endforelse

                                    {{-- 4. Masuk Bulan Ini --}}
                                    <td class="w-[110px] min-w-[110px] px-2 py-2 text-center border-r border-gray-200 dark:border-gray-800 bg-indigo-50/40 dark:bg-indigo-950/30 whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-black {{ $totalBulanIni > 0 ? 'bg-indigo-100 text-indigo-900 dark:bg-indigo-900/70 dark:text-indigo-200 border border-indigo-200 dark:border-indigo-800' : 'text-gray-400' }}">
                                            {{ $totalBulanIni > 0 ? "+{$totalBulanIni}" : '0' }}
                                        </span>
                                    </td>

                                    {{-- 5. Aksi: Tombol Detail (Menggantikan Kolom Status) --}}
                                    <td class="w-[80px] min-w-[80px] px-2 py-2 text-center">
                                        <button
                                            type="button"
                                            wire:click="openDetailModal({{ $item->id }})"
                                            class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-2 py-1 text-xs font-bold text-indigo-700 hover:bg-indigo-100 hover:text-indigo-950 dark:bg-indigo-950/60 dark:text-indigo-300 dark:hover:bg-indigo-900 border border-indigo-200 dark:border-indigo-800 transition-colors shadow-2xs cursor-pointer"
                                            title="Lihat Detail Tanggal & Sumber Pengadaan"
                                        >
                                            <x-filament::icon icon="heroicon-m-eye" class="h-3.5 w-3.5 text-indigo-600 dark:text-indigo-400" />
                                            <span>Detail</span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    @empty
                        <tbody class="bg-white dark:bg-gray-900">
                            <tr>
                                <td colspan="{{ count($activeDates) + 5 }}" class="py-12 text-center text-gray-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <x-filament::icon icon="heroicon-o-cube" class="h-10 w-10 text-gray-300 dark:text-gray-600" />
                                        <p class="text-sm font-medium">Tidak ada barang yang ditemukan.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @endforelse
                </table>
            </div>
        </div>
    </div>

    {{-- ═══ FILAMENT NATIVE MODAL: PEMILIH TANGGAL (STANDARD 7-COLUMN MONTHLY CALENDAR) ═══ --}}
    <x-filament::modal id="add-date-modal" width="md">
        <x-slot name="heading">
            <div class="flex items-center gap-2 text-sm font-bold">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-teal-100 text-teal-700 dark:bg-teal-900/50 dark:text-teal-300">
                    <x-filament::icon icon="heroicon-m-calendar-days" class="h-4 w-4" />
                </span>
                <span>Pilih Tanggal Masuk ({{ $this->selectedPeriodLabel }})</span>
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
                        {{-- TANGGAL SUDAH DIBUKA (HIJAU SOLID & TEGAS) --}}
                        <button
                            type="button"
                            wire:click="addDateColumn('{{ $cell['date'] }}')"
                            x-on:click="$dispatch('close-modal', { id: 'add-date-modal' })"
                            style="background-color: #10b981 !important; border: 2px solid #059669 !important; color: #ffffff !important; border-radius: 12px; height: 44px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; font-weight: 900; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.35);"
                            class="transition-all hover:opacity-90 active:scale-95"
                            title="Tanggal {{ $cell['day'] }} sudah dibuka di tabel (Klik untuk pilih lagi)"
                        >
                            <span style="font-size: 15px; line-height: 1; font-weight: 900; color: #ffffff !important;">{{ $cell['day'] }}</span>
                            <span style="font-size: 8px; line-height: 1; font-weight: 900; color: #d1fae5 !important; margin-top: 3px; letter-spacing: 0.5px;">✓ DIBUKA</span>
                        </button>
                    @else
                        {{-- TANGGAL BELUM DIBUKA (PUTIH / MERAH UNTUK MINGGU) --}}
                        <button
                            type="button"
                            wire:click="addDateColumn('{{ $cell['date'] }}')"
                            x-on:click="$dispatch('close-modal', { id: 'add-date-modal' })"
                            style="background-color: {{ $cell['isSunday'] ? '#fff1f2' : '#ffffff' }} !important; border: 1.5px solid {{ $cell['isSunday'] ? '#fecdd3' : '#e2e8f0' }} !important; color: {{ $cell['isSunday'] ? '#e11d48' : '#0f172a' }} !important; border-radius: 12px; height: 44px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; font-weight: 800;"
                            class="transition-all hover:border-teal-500 hover:shadow-xs active:scale-95"
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
                    <span style="display: inline-block; width: 14px; height: 14px; background-color: #10b981; border-radius: 4px; box-shadow: 0 1px 2px rgba(16, 185, 129, 0.4);"></span>
                    <span><strong style="color: #059669; font-weight: 900;">Warna Hijau (✓ DIBUKA)</strong>: Sudah aktif di tabel</span>
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

    {{-- ═══ MODAL KELOLA / PINDAH / HAPUS TANGGAL & DATA ═══ --}}
    @if ($showDeleteConfirmModal && $dateToDelete)
        @php
            $formattedDeleteDate = \Carbon\Carbon::parse($dateToDelete)->translatedFormat('l, d F Y');
            $txCount = $this->dateTransactionCount;
        @endphp
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-950/70 backdrop-blur-xs transition-opacity"
            style="position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.65); display: flex; align-items: center; justify-content: center;"
            wire:keydown.escape="cancelDeleteDate"
        >
            <div
                class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800"
                @click.away="$wire.cancelDeleteDate()"
            >
                {{-- Header Modal --}}
                <div class="flex items-start gap-3.5 pb-4 border-b border-gray-200 dark:border-gray-800">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400">
                        <svg class="h-6 w-6 stroke-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>

                    <div class="flex-1">
                        <h3 class="text-base font-extrabold text-gray-900 dark:text-white">
                            Kelola Kolom Tanggal: {{ $formattedDeleteDate }}
                        </h3>
                        <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                            @if ($txCount > 0)
                                Terdeteksi <strong class="text-indigo-600 dark:text-indigo-400 font-bold">{{ $txCount }} catatan barang masuk</strong> pada tanggal ini.
                            @else
                                Belum ada data penerimaan barang yang diinput pada tanggal ini.
                            @endif
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="cancelDeleteDate"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1 cursor-pointer"
                    >
                        ✕
                    </button>
                </div>

                {{-- Content Options --}}
                <div class="mt-4 space-y-4">
                    {{-- Opsi 1: Pindahkan Tanggal (Jika Salah Pilih Tanggal) --}}
                    <div class="rounded-xl border border-indigo-200 bg-indigo-50/70 p-4 dark:border-indigo-900/60 dark:bg-indigo-950/40">
                        <div class="flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-indigo-600 text-white text-[10px] font-bold">
                                ⇄
                            </span>
                            <h4 class="text-xs font-bold text-indigo-950 dark:text-indigo-200">
                                Pindahkan ke Tanggal Lain (Salah Pilih Tanggal)
                            </h4>
                        </div>

                        <p class="mt-1.5 text-[11px] text-indigo-900/80 dark:text-indigo-300 leading-relaxed">
                            Jika Anda keliru memilih tanggal saat input, pindahkan seluruh data masuk ini ke tanggal tujuan yang benar tanpa perlu mengetik ulang dari awal:
                        </p>

                        <div class="mt-3 flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                            {{-- Input Ketikan Tanggal (Bisa Diketik Langsung / Dipilih) --}}
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

                    {{-- Opsi 2: Hapus Permanen (Jika Data Batal / Salah Input) --}}
                    <div class="rounded-xl border border-red-200 bg-red-50/70 p-4 dark:border-red-900/60 dark:bg-red-950/40">
                        <div class="flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-white text-[10px] font-bold">
                                🗑
                            </span>
                            <h4 class="text-xs font-bold text-red-950 dark:text-red-200">
                                Hapus Permanen Tanggal & Data Ini
                            </h4>
                        </div>

                        <p class="mt-1.5 text-[11px] text-red-900/80 dark:text-red-300 leading-relaxed">
                            Seluruh catatan penerimaan barang pada tanggal ini akan <strong>dihapus</strong> dan stok fisik barang di lab akan <strong>dikurangi kembali secara otomatis</strong>.
                        </p>

                        <div class="mt-3 flex justify-end">
                            <x-filament::button
                                color="danger"
                                icon="heroicon-m-trash"
                                wire:click="executeDeleteDate"
                            >
                                Ya, Hapus Tanggal & Data Ini
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
                    <span class="rounded bg-indigo-100 px-1.5 py-0.5 font-mono text-xs font-black text-indigo-700 dark:bg-indigo-900/80 dark:text-indigo-300">
                        {{ $detailItem->kode }}
                    </span>
                    <span class="text-base font-bold text-gray-950 dark:text-white">
                        {{ $detailItem->nama }}
                    </span>
                </div>
            </x-slot>

            <x-slot name="description">
                Kategori: {{ $detailItem->category?->nama ?? 'Tanpa Kategori' }} | Lokasi: {{ $detailItem->room?->nama ?? 'Belum Ditentukan' }} | Stok Saat Ini: <strong class="text-indigo-600 dark:text-indigo-400">{{ $detailItem->stock?->stok_tersedia ?? 0 }} {{ $detailItem->satuan }}</strong>
            </x-slot>

            <div class="space-y-5 py-2">
                {{-- Quick Action: Terapkan Satu Sumber ke Semua Tanggal untuk Barang Ini --}}
                <div class="rounded-xl border border-indigo-200 bg-indigo-50/60 p-3.5 dark:border-indigo-900/60 dark:bg-indigo-950/30">
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                        <div>
                            <h4 class="text-xs font-bold text-indigo-950 dark:text-indigo-200">
                                ⚡ Terapkan Sumber ke Seluruh Tanggal Masuk Barang Ini
                            </h4>
                            <p class="text-[11px] text-indigo-800/80 dark:text-indigo-300">
                                Ubah semua catatan penerimaan barang ini di bulan {{ $this->selectedPeriodLabel }} menjadi satu sumber sekaligus.
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <select
                                wire:model="detailQuickSource"
                                class="rounded-lg border-gray-300 py-1.5 px-2.5 text-xs font-bold text-gray-900 shadow-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >
                                @foreach (\App\Filament\Kepalab\Pages\BarangMasukPage::SUMBER_OPTIONS as $optKey => $optLabel)
                                    <option value="{{ $optKey }}">{{ $optLabel }}</option>
                                @endforeach
                            </select>

                            <button
                                type="button"
                                wire:click="applySourceToAllDetailTransactions($wire.detailQuickSource)"
                                class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-indigo-700 shadow-xs cursor-pointer whitespace-nowrap"
                            >
                                Terapkan
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Tabel Riwayat Tanggal & Sumber --}}
                <div>
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-2">
                        Riwayat Tanggal Masuk (Bulan {{ $this->selectedPeriodLabel }})
                    </h4>

                    @if (empty($detailTransactions))
                        <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-xs text-gray-500 dark:border-gray-700">
                            Belum ada catatan penerimaan barang masuk pada bulan ini. Silakan isi angka pada kolom tanggal di tabel.
                        </div>
                    @else
                        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800 shadow-2xs">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold border-b border-gray-200 dark:border-gray-800">
                                    <tr>
                                        <th class="px-3 py-2.5">Tanggal Masuk</th>
                                        <th class="px-3 py-2.5 text-center">Jumlah (Qty)</th>
                                        <th class="px-3 py-2.5">Sumber Pengadaan / Asal Barang</th>
                                        <th class="px-3 py-2.5 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach ($detailTransactions as $dtx)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-gray-800/40" x-data="{ editing: false, sumber: '{{ $dtx['sumber'] }}' }">
                                            <td class="px-3 py-2.5 font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                                {{ $dtx['tgl_label'] }}
                                            </td>
                                            <td class="px-3 py-2.5 text-center">
                                                <span class="inline-flex rounded-md bg-emerald-100 px-2 py-0.5 font-black text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                                    +{{ $dtx['qty'] }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2.5">
                                                <template x-if="!editing">
                                                    <div class="flex items-center">
                                                        <span class="rounded-lg bg-indigo-50 px-2.5 py-1 font-semibold text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                                            {{ $dtx['sumber'] }}
                                                        </span>
                                                    </div>
                                                </template>
                                                <template x-if="editing">
                                                    <div class="flex items-center gap-1.5">
                                                        <select
                                                            x-model="sumber"
                                                            class="rounded border-gray-300 py-1 px-2 text-xs font-semibold dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                        >
                                                            @foreach (\App\Filament\Kepalab\Pages\BarangMasukPage::SUMBER_OPTIONS as $optKey => $optLabel)
                                                                <option value="{{ $optKey }}">{{ $optLabel }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button
                                                            type="button"
                                                            @click="$wire.updateDetailTransaction({{ $dtx['id'] }}, sumber); editing = false"
                                                            class="rounded bg-emerald-600 px-2.5 py-1 text-[11px] font-bold text-white hover:bg-emerald-700 cursor-pointer"
                                                            title="Simpan Sumber"
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
                                                    class="text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 text-xs font-semibold underline cursor-pointer"
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

    {{-- ═══ MODAL ATUR SUMBER MASSAL (POLA DAFTARKAN BARANG) ═══ --}}
    @if ($showBulkSourceModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs">
            <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800 animate-in fade-in zoom-in-95 duration-150">
                {{-- Header --}}
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800 bg-indigo-50/60 dark:bg-indigo-950/40 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-xs">
                            <x-filament::icon icon="heroicon-m-squares-plus" class="h-5 w-5" />
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-gray-950 dark:text-white">
                                ⚡ Atur Sumber Pengadaan Massal
                            </h3>
                            <p class="text-[11px] text-indigo-700 dark:text-indigo-300 font-bold">
                                {{ count($selectedItemIds) }} barang telah diceklis
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        wire:click="closeBulkSourceModal"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer"
                    >
                        ✕
                    </button>
                </div>

                {{-- Form Body --}}
                <div class="p-6 space-y-4">
                    <div class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-3 text-xs text-indigo-950 dark:border-indigo-900/40 dark:bg-indigo-950/20 dark:text-indigo-200 leading-relaxed">
                        Tentukan sumber asal barang masuk sekaligus untuk <strong>{{ count($selectedItemIds) }} barang</strong> yang Anda pilih. Sekali klik, seluruh barang yang diceklis akan langsung ter-update sumbernya!
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-900 dark:text-white mb-1.5">
                            Pilih Sumber Barang Masuk:
                        </label>
                        <select
                            wire:model="bulkSource"
                            class="w-full rounded-lg border-gray-300 py-2 px-3 text-xs font-bold text-gray-900 shadow-xs focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                            @foreach (\App\Filament\Kepalab\Pages\BarangMasukPage::SUMBER_OPTIONS as $optKey => $optLabel)
                                <option value="{{ $optKey }}">{{ $optLabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-900 dark:text-white mb-1.5">
                            Atau Tulis Sumber Kustom (Jika tidak ada di daftar):
                        </label>
                        <input
                            type="text"
                            wire:model="bulkCustomSource"
                            placeholder="Contoh: Bantuan Alumni Angkatan 2022, CSR PT Astra Honda"
                            class="w-full rounded-lg border-gray-300 py-2 px-3 text-xs shadow-xs focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-900 dark:text-white mb-1.5">
                            Terapkan Untuk Tanggal:
                        </label>
                        <select
                            wire:model="bulkTargetDate"
                            class="w-full rounded-lg border-gray-300 py-2 px-3 text-xs font-bold text-gray-900 shadow-xs focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="all">★ Semua Tanggal Masuk di Bulan Ini ({{ $this->selectedPeriodLabel }})</option>
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
                        wire:click="closeBulkSourceModal"
                        class="text-xs font-bold text-gray-600 hover:text-gray-900 dark:text-gray-400 cursor-pointer"
                    >
                        Batal
                    </button>

                    <x-filament::button
                        color="success"
                        icon="heroicon-m-check-badge"
                        wire:click="executeBulkSource"
                    >
                        ✓ Terapkan ke {{ count($selectedItemIds) }} Barang
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
