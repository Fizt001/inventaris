<x-filament-panels::page>
    @php
        $selectedRoom = $this->selectedRoom;
    @endphp

    {{-- ══════════════════════════════════════════════════════════════════════════════════ --}}
    {{-- CSS LAYOUT 2-CARD: BERSIH, LEGA, FIXED SEARCH/TITLES & SCROLLABLE LISTS            --}}
    {{-- ══════════════════════════════════════════════════════════════════════════════════ --}}
    <style>
        /* Kurangi padding bawaan Filament page agar pas di layar */
        .fi-page section {
            padding-top: 0.25rem !important;
            padding-bottom: 0.5rem !important;
            gap: 0.75rem !important;
        }

        .mu-main-wrapper {
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        /* Header Judul Halaman */
        .mu-page-header {
            margin-bottom: 1rem;
        }

        /* ─── LAYOUT 2 KOLOM (CARD 1 RUANGAN & CARD 2 AREA LAB) ─── */
        .mu-split-grid {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            width: 100%;
        }
        @media (min-width: 1024px) {
            .mu-split-grid {
                display: grid !important;
                grid-template-columns: 420px minmax(0, 1fr) !important;
                gap: 1.5rem !important;
                align-items: stretch !important;
            }
        }

        /* Card Kolom Kiri dan Kanan */
        .mu-column-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            display: flex !important;
            flex-direction: column !important;
            height: calc(100vh - 170px) !important;
            min-height: 520px !important;
            padding: 1.25rem 1.25rem 1rem 1.25rem !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
        }
        .dark .mu-column-card {
            background-color: #111827;
            border-color: rgba(255, 255, 255, 0.1);
        }

        /* Header & Search di dalam Kolom (TIDAK BERGESER / TETAP) */
        .mu-column-fixed-header {
            flex-shrink: 0 !important;
            width: 100% !important;
        }

        .mu-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 0.875rem !important;
            margin-bottom: 1rem !important;
            border-bottom: 1px solid #f1f5f9;
        }
        .dark .mu-panel-header {
            border-bottom-color: #1f2937;
        }

        /* Area List Card yang BERGESER / SCROLLABLE */
        .mu-column-scroll-area {
            flex: 1 1 auto !important;
            min-height: 0 !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            padding-right: 0.4rem !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 0.75rem !important;
        }

        /* Subcard Item */
        .mu-item-card {
            border-radius: 1rem;
            padding: 1.125rem 1.25rem !important;
            transition: all 0.2s ease;
            position: relative;
            flex-shrink: 0 !important;
        }

        .mu-item-card-unselected {
            border: 1px solid #e2e8f0;
            background-color: #ffffff;
        }
        .dark .mu-item-card-unselected {
            border-color: rgba(255, 255, 255, 0.08);
            background-color: #1f2937;
        }
        .mu-item-card-unselected:hover {
            border-color: #93c5fd;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .mu-item-card-selected {
            border: 2px solid #3b82f6 !important;
            background-color: #eff6ff !important;
            box-shadow: 0 4px 10px -1px rgba(59, 130, 246, 0.2) !important;
        }
        .dark .mu-item-card-selected {
            border-color: #60a5fa !important;
            background-color: rgba(30, 58, 138, 0.35) !important;
        }

        /* Form Card Tambah Area Baru */
        .mu-new-area-card {
            border: 2px dashed #60a5fa;
            background-color: #f0f7ff;
            border-radius: 1rem;
            padding: 1.125rem 1.25rem !important;
            flex-shrink: 0 !important;
        }
        .dark .mu-new-area-card {
            border-color: #3b82f6;
            background-color: rgba(30, 58, 138, 0.2);
        }

        /* Custom Scrollbar Ramping & Modern */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #374151;
        }
    </style>

    <div class="mu-main-wrapper">

        {{-- Judul Halaman --}}
        <div class="mu-page-header">
            <h1 class="fi-header-heading text-2xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl">
                Ruang & Area Laboratorium
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Pilih ruangan di sebelah kiri untuk melihat dan mengelola area laboratorium di dalamnya.
            </p>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════════════════════ --}}
        {{-- LAYOUT MURNI 2 KOLOM (CARD 1 RUANG & CARD 2 AREA LAB)                              --}}
        {{-- ══════════════════════════════════════════════════════════════════════════════════ --}}
        <div class="mu-split-grid">

            {{-- ────────────────────────────────────────────────────────────────────────── --}}
            {{-- KOLOM KIRI (CARD 1): DAFTAR RUANG & LAB (MASTER / PEMILIH RUANGAN)         --}}
            {{-- ────────────────────────────────────────────────────────────────────────── --}}
            <div class="mu-column-card">
                {{-- Bagian Atas Tetap: Header & Kotak Pencarian --}}
                <div class="mu-column-fixed-header">
                    {{-- Baris 1: Header Kolom Kiri --}}
                    <div class="mu-panel-header" style="padding-bottom: 0.625rem; margin-bottom: 0.625rem;">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400">
                                <x-filament::icon icon="heroicon-o-building-office" class="h-4 w-4" />
                            </div>
                            <h3 class="text-sm font-extrabold uppercase tracking-wider text-gray-900 dark:text-white truncate">
                                Daftar Ruang & Lab
                            </h3>
                            <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-bold text-blue-800 dark:bg-blue-900/60 dark:text-blue-300">
                                {{ $this->rooms->count() }}
                            </span>
                        </div>
                    </div>

                    {{-- Baris 2: Kotak Pencarian Ruang (TETAP / TIDAK TERGESER) --}}
                    <div class="relative flex items-center" style="height: 38px; margin-bottom: 0.875rem;">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <x-filament::icon icon="heroicon-m-magnifying-glass" class="h-4 w-4 text-gray-400" />
                        </div>
                        <input
                            type="text"
                            wire:model.live.debounce.250ms="searchRoom"
                            placeholder="Cari ruang, kode, atau unit..."
                            class="w-full h-full rounded-xl border border-gray-200/90 bg-gray-50/50 pl-9 pr-3 text-xs text-gray-900 shadow-2xs placeholder:text-gray-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 dark:border-white/10 dark:bg-gray-800 dark:text-white"
                        />
                    </div>
                </div>

                {{-- Area List Card Ruangan (HANYA BAGIAN INI YANG BERGESER / SCROLLABLE) --}}
                <div class="mu-column-scroll-area custom-scrollbar">
                    @forelse ($this->rooms as $r)
                        @php
                            $isSelected = $selectedRoomId === $r->id;
                        @endphp
                        <div
                            wire:key="room-card-{{ $r->id }}"
                            wire:click="selectRoom({{ $r->id }})"
                            class="mu-item-card {{ $isSelected ? 'mu-item-card-selected' : 'mu-item-card-unselected' }} cursor-pointer select-none"
                        >
                            {{-- Baris Atas: Kode, Unit Induk & Status Terpilih --}}
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span
                                        style="{{ $isSelected ? 'background-color: #2563eb; color: #ffffff;' : 'background-color: #f1f5f9; color: #1e293b; border: 1px solid #cbd5e1;' }}"
                                        class="rounded-md font-mono text-xs font-extrabold px-2.5 py-0.5"
                                    >
                                        {{ $r->kode ?: 'LAB' }}
                                    </span>

                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/50 dark:text-indigo-300">
                                        {{ $r->unit?->kode ?: ($r->unit?->nama ?? 'Umum') }}
                                    </span>
                                </div>

                                @if ($isSelected)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-700 dark:text-blue-300 bg-blue-100/90 dark:bg-blue-900/60 px-2 py-0.5 rounded-full">
                                        <x-filament::icon icon="heroicon-m-check-circle" class="h-3.5 w-3.5 text-blue-600" />
                                        <span>Aktif Dipilih</span>
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 group-hover:text-blue-500">
                                        Pilih Ruangan &rarr;
                                    </span>
                                @endif
                            </div>

                            {{-- Nama Ruangan --}}
                            <div class="text-sm font-extrabold {{ $isSelected ? 'text-blue-950 dark:text-blue-100' : 'text-gray-900 dark:text-white' }}">
                                {{ $r->nama }}
                            </div>

                            {{-- Unit Lengkap --}}
                            <div class="text-[11px] font-medium text-gray-500 dark:text-gray-400 mt-0.5">
                                Unit: {{ $r->unit?->nama ?? 'Umum' }}
                            </div>

                            {{-- Info Footer: Penanggung Jawab & Jumlah Area --}}
                            <div class="mt-2.5 pt-2 border-t {{ $isSelected ? 'border-blue-200/80 dark:border-blue-900/60' : 'border-gray-100 dark:border-gray-800' }} flex items-center justify-between text-xs">
                                <div class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400 truncate max-w-[190px]">
                                    <x-filament::icon icon="heroicon-m-user" class="h-3.5 w-3.5 shrink-0 text-gray-400" />
                                    <span class="truncate">{{ $r->penanggung_jawab ?: 'Belum ada PJ' }}</span>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-bold text-[11px] {{
                                        $r->areas_count > 0
                                            ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800'
                                            : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'
                                    }}">
                                        <x-filament::icon icon="heroicon-m-squares-plus" class="h-3 w-3" />
                                        <span>{{ $r->areas_count }} area</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center dark:border-gray-700">
                            <x-filament::icon icon="heroicon-o-building-office" class="mx-auto h-8 w-8 text-gray-400" />
                            <p class="mt-2 text-xs font-semibold text-gray-500">Tidak ada ruangan yang cocok dengan pencarian.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- ────────────────────────────────────────────────────────────────────────── --}}
            {{-- KOLOM KANAN (CARD 2): PANEL KELOLA AREA LAB (DETAIL)                       --}}
            {{-- ────────────────────────────────────────────────────────────────────────── --}}
            <div class="mu-column-card">
                @if ($selectedRoom)
                    {{-- Bagian Atas Tetap: Header Area Lab --}}
                    <div class="mu-column-fixed-header">
                        {{-- Baris 1: Judul Ruang Terpilih --}}
                        <div class="mu-panel-header" style="padding-bottom: 0.625rem; margin-bottom: 0.625rem;">
                            <div class="flex items-center gap-2 min-w-0 w-full">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400">
                                    <x-filament::icon icon="heroicon-o-squares-plus" class="h-4 w-4" />
                                </div>
                                <h3 class="text-sm font-extrabold uppercase tracking-wider text-gray-900 dark:text-white truncate">
                                    Area Lab: <span class="text-blue-600 dark:text-blue-400">{{ $selectedRoom->nama }}</span>
                                    <span class="text-xs font-normal text-gray-500 dark:text-gray-400 ml-1">({{ $selectedRoom->unit?->nama }})</span>
                                </h3>
                            </div>
                        </div>

                        {{-- Baris 2: Badge Jumlah Area (Kiri) & Tambah Area (Kanan) --}}
                        <div class="flex items-center justify-between gap-2" style="height: 38px; margin-bottom: 0.875rem;">
                            {{-- Kiri: Badge Jumlah Area --}}
                            <div class="flex items-center gap-1.5">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-3 py-1.5 text-xs font-bold text-blue-800 dark:bg-blue-900/60 dark:text-blue-300">
                                    <x-filament::icon icon="heroicon-m-squares-plus" class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400" />
                                    <span>{{ $selectedRoom->areas->count() + count($newAreas) }} Area</span>
                                </span>
                            </div>

                            {{-- Kanan: Tombol "+ Tambah Area" --}}
                            <button
                                type="button"
                                wire:click="addNewAreaCard"
                                style="background-color: #2563eb; color: #ffffff;"
                                class="inline-flex items-center justify-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-bold shadow-sm hover:opacity-90 active:scale-95 cursor-pointer transition shrink-0"
                                title="Klik untuk memunculkan form penambahan area lab baru"
                            >
                                <x-filament::icon icon="heroicon-m-plus" class="h-4 w-4 text-white" />
                                <span>Tambah Area</span>
                            </button>
                        </div>
                    </div>

                    {{-- Area Scrollable Area Lab (HANYA BAGIAN INI YANG BERGESER) --}}
                    <div class="mu-column-scroll-area custom-scrollbar">

                        {{-- ═══ SECTION A: DAFTAR CARD AREA BARU YANG SEDANG DITAMBAHKAN ═══ --}}
                        @if (count($newAreas) > 0)
                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                @foreach ($newAreas as $idx => $nArea)
                                    <div
                                        wire:key="new-area-card-{{ $nArea['temp_id'] }}"
                                        class="mu-new-area-card"
                                    >
                                        {{-- Header Card Baru + Tanda Silang (✕) --}}
                                        <div class="flex items-center justify-between pb-2 mb-2.5 border-b border-blue-200/80 dark:border-blue-900/60">
                                            <div class="flex items-center gap-2">
                                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-white font-extrabold text-[11px]">
                                                    {{ $idx + 1 }}
                                                </span>
                                                <span class="text-xs font-bold uppercase tracking-wider text-blue-900 dark:text-blue-200">
                                                    Tambah Area Baru #{{ $idx + 1 }}
                                                </span>
                                            </div>

                                            <button
                                                type="button"
                                                wire:click="removeNewAreaCard({{ $idx }})"
                                                class="inline-flex items-center justify-center p-1 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/50 transition cursor-pointer"
                                                title="Batalkan / Hapus Card Area Ini"
                                            >
                                                <x-filament::icon icon="heroicon-m-x-mark" class="h-4 w-4 text-gray-500 hover:text-red-600" />
                                            </button>
                                        </div>

                                        {{-- Inputs Card Area Baru: Compact Horizontal Grid --}}
                                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)) 80px auto; gap: 0.75rem; align-items: end;">
                                            {{-- Nama Area --}}
                                            <div>
                                                <label class="block text-xs font-medium text-gray-950 dark:text-white mb-1">
                                                    Nama Area <span style="color: #dc2626; font-weight: 700;">*</span>
                                                </label>
                                                <input
                                                    type="text"
                                                    list="area-names-list-{{ $idx }}"
                                                    wire:model="newAreas.{{ $idx }}.nama"
                                                    placeholder="Pilih / ketik nama area..."
                                                    class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 shadow-2xs placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                />
                                                <datalist id="area-names-list-{{ $idx }}">
                                                    @foreach ($this->areaNameOptionsList as $aName)
                                                        <option value="{{ $aName }}"></option>
                                                    @endforeach
                                                </datalist>
                                            </div>

                                            {{-- Kode Area --}}
                                            <div>
                                                <label class="block text-xs font-medium text-gray-950 dark:text-white mb-1">
                                                    Kode Area
                                                </label>
                                                <input
                                                    type="text"
                                                    wire:model="newAreas.{{ $idx }}.kode"
                                                    placeholder="COE-01"
                                                    style="text-transform: uppercase;"
                                                    class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-mono font-bold text-gray-900 shadow-2xs placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                />
                                            </div>

                                            {{-- Kategori Area --}}
                                            <div>
                                                <label class="block text-xs font-medium text-gray-950 dark:text-white mb-1">
                                                    Kategori
                                                </label>
                                                <select
                                                    wire:model="newAreas.{{ $idx }}.kategori"
                                                    class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 shadow-2xs focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer"
                                                >
                                                    @foreach ($this->kategoriAreaOptions as $kVal => $kLabel)
                                                        <option value="{{ $kVal }}">{{ $kLabel }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            {{-- Penanggung Jawab Area --}}
                                            <div>
                                                <label class="block text-xs font-medium text-gray-950 dark:text-white mb-1">
                                                    Penanggung Jawab (PJ)
                                                </label>
                                                <input
                                                    type="text"
                                                    wire:model="newAreas.{{ $idx }}.penanggung_jawab"
                                                    placeholder="Nama guru / laboran"
                                                    class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 shadow-2xs placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                />
                                            </div>

                                            {{-- Checkbox & Tombol Simpan --}}
                                            <div class="flex items-center gap-1.5" style="padding-bottom: 2px;">
                                                <label class="inline-flex items-center gap-1.5 cursor-pointer select-none rounded-lg border border-gray-200 dark:border-gray-700 px-2.5 py-1.5 bg-white dark:bg-gray-800 text-xs font-bold text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                                    <input
                                                        type="checkbox"
                                                        wire:model="newAreas.{{ $idx }}.aktif"
                                                        class="h-3.5 w-3.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                                    />
                                                    <span>Aktif</span>
                                                </label>

                                                <button
                                                    type="button"
                                                    wire:click="saveNewArea({{ $idx }})"
                                                    style="background-color: #059669; color: #ffffff;"
                                                    class="inline-flex items-center justify-center gap-1 rounded-lg px-3.5 py-1.5 text-xs font-bold shadow-2xs hover:opacity-90 active:scale-95 cursor-pointer transition whitespace-nowrap"
                                                >
                                                    <x-filament::icon icon="heroicon-m-check" class="h-3.5 w-3.5 text-white" />
                                                    <span>Simpan</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- ═══ SECTION B: DAFTAR AREA LAB YANG SUDAH TERSIMPAN ═══ --}}
                        @forelse ($selectedRoom->areas as $area)
                            <div
                                wire:key="existing-area-card-{{ $area->id }}"
                                class="mu-item-card mu-item-card-unselected"
                            >
                                @if ($editingAreaId === $area->id)
                                    {{-- Form Edit Inline Area --}}
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-800">
                                            <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">
                                                Edit Area: {{ $area->nama }}
                                            </span>
                                            <button
                                                type="button"
                                                wire:click="cancelEditArea"
                                                class="text-xs font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer"
                                            >
                                                Batal
                                            </button>
                                        </div>

                                        {{-- Edit Area: Compact Horizontal Grid --}}
                                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)) 80px auto; gap: 0.75rem; align-items: end;">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-950 dark:text-white mb-1">
                                                    Nama Area <span style="color: #dc2626; font-weight: 700;">*</span>
                                                </label>
                                                <input
                                                    type="text"
                                                    wire:model="editingAreaData.nama"
                                                    class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                />
                                            </div>

                                            <div>
                                                <label class="block text-xs font-medium text-gray-950 dark:text-white mb-1">Kode Area</label>
                                                <input
                                                    type="text"
                                                    wire:model="editingAreaData.kode"
                                                    style="text-transform: uppercase;"
                                                    class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-mono font-bold text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                />
                                            </div>

                                            <div>
                                                <label class="block text-xs font-medium text-gray-950 dark:text-white mb-1">Kategori</label>
                                                <select
                                                    wire:model="editingAreaData.kategori"
                                                    class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer"
                                                >
                                                    @foreach ($this->kategoriAreaOptions as $kVal => $kLabel)
                                                        <option value="{{ $kVal }}">{{ $kLabel }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-medium text-gray-950 dark:text-white mb-1">Penanggung Jawab</label>
                                                <input
                                                    type="text"
                                                    wire:model="editingAreaData.penanggung_jawab"
                                                    placeholder="Nama PJ"
                                                    class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                />
                                            </div>

                                            <div class="flex items-center gap-1.5" style="padding-bottom: 2px;">
                                                <label class="inline-flex items-center gap-1.5 cursor-pointer rounded-lg border border-gray-200 dark:border-gray-700 px-2.5 py-1.5 bg-white dark:bg-gray-800 text-xs font-bold text-gray-700 dark:text-gray-300">
                                                    <input
                                                        type="checkbox"
                                                        wire:model="editingAreaData.aktif"
                                                        class="h-3.5 w-3.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                                    />
                                                    <span>Aktif</span>
                                                </label>

                                                <button
                                                    type="button"
                                                    wire:click="updateArea({{ $area->id }})"
                                                    style="background-color: #2563eb; color: #ffffff;"
                                                    class="inline-flex items-center justify-center gap-1 rounded-lg px-3.5 py-1.5 text-xs font-bold shadow-2xs hover:opacity-90 active:scale-95 cursor-pointer transition whitespace-nowrap"
                                                >
                                                    <x-filament::icon icon="heroicon-m-check" class="h-3.5 w-3.5 text-white" />
                                                    <span>Simpan</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    {{-- Tampilan Normal Card Area Lab --}}
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex-1 min-w-0">
                                            {{-- Baris Atas: Kode & Kategori --}}
                                            <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                                                <span class="rounded-md font-mono text-xs font-extrabold px-2.5 py-0.5 bg-slate-100 text-slate-800 border border-slate-300 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700">
                                                    {{ $area->kode ?: 'AREA' }}
                                                </span>

                                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full {{
                                                    $area->kategori === 'praktik' ? 'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/50 dark:text-blue-300' :
                                                    ($area->kategori === 'penyimpanan' ? 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/50 dark:text-amber-300' :
                                                    ($area->kategori === 'server' ? 'bg-red-50 text-red-700 border border-red-200 dark:bg-red-950/50 dark:text-red-300' :
                                                    ($area->kategori === 'instruktur' ? 'bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/50 dark:text-purple-300' :
                                                    'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300')))
                                                }}">
                                                    {{ \App\Models\RoomArea::KATEGORI_LABELS[$area->kategori] ?? ucfirst($area->kategori ?? 'praktik') }}
                                                </span>

                                                {{-- Quick Toggle Aktif/Nonaktif --}}
                                                <button
                                                    type="button"
                                                    wire:click="toggleAreaActive({{ $area->id }})"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold cursor-pointer transition {{
                                                        $area->aktif
                                                            ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 dark:bg-emerald-950/50 dark:text-emerald-300'
                                                            : 'bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 dark:bg-red-950/50 dark:text-red-400'
                                                    }}"
                                                    title="Klik untuk ubah status aktif/nonaktif"
                                                >
                                                    <span class="h-1.5 w-1.5 rounded-full {{ $area->aktif ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                                    <span>{{ $area->aktif ? 'Aktif' : 'Nonaktif' }}</span>
                                                </button>
                                            </div>

                                            {{-- Nama Area Lab --}}
                                            <div class="text-sm font-bold text-gray-900 dark:text-white">
                                                {{ $area->nama }}
                                            </div>

                                            {{-- Detail PJ --}}
                                            <div class="mt-2 flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                                <span class="flex items-center gap-1">
                                                    <x-filament::icon icon="heroicon-m-user" class="h-3.5 w-3.5 text-gray-400" />
                                                    <span>PJ: {{ $area->penanggung_jawab ?: 'Belum ditentukan' }}</span>
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Tombol Aksi: Edit & Hapus --}}
                                        <div class="flex items-center gap-1 shrink-0">
                                            <button
                                                type="button"
                                                wire:click="editArea({{ $area->id }})"
                                                class="inline-flex items-center justify-center p-1.5 rounded-lg text-gray-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/50 transition cursor-pointer"
                                                title="Edit Area Ini"
                                            >
                                                <x-filament::icon icon="heroicon-m-pencil-square" class="h-4 w-4" />
                                            </button>

                                            <button
                                                type="button"
                                                wire:confirm="Yakin ingin menghapus area '{{ $area->nama }}'? Tindakan ini tidak dapat dibatalkan."
                                                wire:click="deleteArea({{ $area->id }})"
                                                class="inline-flex items-center justify-center p-1.5 rounded-lg text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/50 transition cursor-pointer"
                                                title="Hapus Area Ini"
                                            >
                                                <x-filament::icon icon="heroicon-m-trash" class="h-4 w-4 text-red-500" />
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @empty
                            @if (count($newAreas) === 0)
                                <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center dark:border-gray-700">
                                    <div class="flex h-12 w-12 mx-auto items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400">
                                        <x-filament::icon icon="heroicon-o-squares-plus" class="h-6 w-6" />
                                    </div>
                                    <h4 class="mt-3 text-sm font-bold text-gray-900 dark:text-white">Belum Ada Area Lab</h4>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Ruangan <strong>{{ $selectedRoom->nama }}</strong> belum memiliki area lab spesifik.
                                    </p>
                                    <button
                                        type="button"
                                        wire:click="addNewAreaCard"
                                        style="background-color: #2563eb; color: #ffffff;"
                                        class="mt-4 inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs font-bold shadow-sm hover:opacity-90 active:scale-95 cursor-pointer transition"
                                    >
                                        <x-filament::icon icon="heroicon-m-plus" class="h-4 w-4 text-white" />
                                        <span>Tambah Area Lab Pertama</span>
                                    </button>
                                </div>
                            @endif
                        @endforelse
                    </div>
                @else
                    {{-- Empty State Jika Tidak Ada Ruangan yang Dipilih --}}
                    <div class="flex flex-col items-center justify-center h-full text-center p-8">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800">
                            <x-filament::icon icon="heroicon-o-building-office" class="h-7 w-7" />
                        </div>
                        <h4 class="mt-3 text-sm font-bold text-gray-900 dark:text-white">Pilih Ruangan di Sebelah Kiri</h4>
                        <p class="mt-1 text-xs text-gray-500 max-w-sm">
                            Pilih salah satu ruangan atau lab di kolom kiri untuk melihat, menambah, dan mengelola area laboratoriumnya.
                        </p>
                    </div>
                @endif
            </div>

        </div>

    </div>
</x-filament-panels::page>
