<x-filament-panels::page>
    @php
        $selectedUnit = $this->selectedUnit;
    @endphp

    {{-- ══════════════════════════════════════════════════════════════════════════════════ --}}
    {{-- CSS SPESIFIK & PASTI: STICKY HEADER, FIXED SEARCH/TITLES, SCROLLABLE CARD LISTS     --}}
    {{-- ══════════════════════════════════════════════════════════════════════════════════ --}}
    <style>
        /* Kurangi padding bawaan Filament page agar muat pas di layar */
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

        /* ─── 1. AREA ATAS TETAP (STICKY): JUDUL HALAMAN & FORM CREATE/EDIT UNIT ─── */
        .mu-sticky-top-block {
            position: sticky !important;
            top: 4rem !important; /* Pas di bawah topbar bawaan Filament */
            z-index: 30 !important;
            background-color: #f9fafb !important;
            padding-top: 0.25rem !important;
            padding-bottom: 0.875rem !important;
        }
        .dark .mu-sticky-top-block {
            background-color: #030712 !important;
        }

        /* Card Atas Ultra-Compact */
        .mu-top-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 0.875rem 1.25rem !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }
        .dark .mu-top-card {
            background-color: #111827;
            border-color: rgba(255, 255, 255, 0.1);
        }

        .mu-top-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 0.625rem !important;
            margin-bottom: 0.75rem !important;
            border-bottom: 1px solid #f1f5f9;
        }
        .dark .mu-top-header {
            border-bottom-color: #1f2937;
        }

        .mu-btn-sm {
            padding: 0.3rem 0.65rem !important;
            font-size: 0.75rem !important;
            border-radius: 0.5rem !important;
            font-weight: 700 !important;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
        }

        /* ─── 2. LAYOUT 2 KOLOM DI BAWAH ─── */
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
            height: calc(100vh - 280px) !important;
            min-height: 480px !important;
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

        /* Area List Card yang BERGESER / SCROLLABLE (Hanya isi card yang scroll) */
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

        /* Subcard Item (Unit Card & Ruangan Card) */
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

        /* Form Card Tambah Ruangan Baru */
        .mu-new-room-card {
            border: 2px dashed #60a5fa;
            background-color: #f0f7ff;
            border-radius: 1rem;
            padding: 1.125rem 1.25rem !important;
            flex-shrink: 0 !important;
        }
        .dark .mu-new-room-card {
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

        {{-- ══════════════════════════════════════════════════════════════════════════════════ --}}
        {{-- AREA ATAS TETAP (STICKY): JUDUL HALAMAN & CARD CREATE/EDIT UNIT TIDAK TERGESER     --}}
        {{-- ══════════════════════════════════════════════════════════════════════════════════ --}}
        <div class="mu-sticky-top-block">
            {{-- Judul Halaman Tetap (Identik dengan header bawaan Filament) --}}
            <div style="margin-bottom: 0.75rem;">
                <h1 class="fi-header-heading text-2xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl">
                    Unit & Laboratorium
                </h1>
            </div>

            {{-- Card Form Edit / Create Unit (Ultra-Compact) --}}
            <div class="mu-top-card">
                {{-- Header Card Atas --}}
                <div class="mu-top-header">
                    {{-- Judul Ringkas di Kiri --}}
                    <div class="flex items-center gap-2 min-w-0">
                        @if ($selectedUnitId)
                            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 shrink-0">
                                <x-filament::icon icon="heroicon-o-pencil-square" class="h-4 w-4" />
                            </div>
                            <div class="flex items-center truncate">
                                <h2 class="text-xs sm:text-sm font-extrabold text-gray-900 dark:text-white truncate">
                                    Edit Unit: <span class="text-blue-600 dark:text-blue-400">{{ $unitForm['nama'] ?: 'Unit' }}</span>
                                </h2>
                            </div>
                        @else
                            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 shrink-0">
                                <x-filament::icon icon="heroicon-o-plus-circle" class="h-4 w-4" />
                            </div>
                            <h2 class="text-xs sm:text-sm font-extrabold text-gray-900 dark:text-white truncate">
                                Tambah Unit / Lab Baru
                            </h2>
                        @endif
                    </div>

                    {{-- Action Buttons Lebih Kecil & Mepet ke Kanan --}}
                    <div class="flex items-center gap-1.5 shrink-0" style="margin-left: auto;">
                        @if ($selectedUnitId)
                            {{-- 1. Tambah Unit Baru --}}
                            <button
                                type="button"
                                wire:click="startCreateUnit"
                                class="mu-btn-sm border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                                title="Beralih ke form buat unit baru"
                            >
                                <x-filament::icon icon="heroicon-m-plus" class="h-3.5 w-3.5 text-emerald-600" />
                                <span>Tambah Unit Baru</span>
                            </button>

                            {{-- 2. Hapus Unit --}}
                            <button
                                type="button"
                                wire:confirm="Yakin ingin menghapus unit ini beserta seluruh ruangannya? Tindakan ini tidak dapat dibatalkan."
                                wire:click="deleteUnit"
                                class="mu-btn-sm border border-red-200 bg-red-50/80 text-red-600 hover:bg-red-100 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-400"
                                title="Hapus unit ini"
                            >
                                <x-filament::icon icon="heroicon-m-trash" class="h-3.5 w-3.5 text-red-500" />
                                <span>Hapus Unit</span>
                            </button>

                            {{-- 3. Simpan Perubahan --}}
                            <button
                                type="button"
                                wire:click="saveUnit"
                                wire:loading.attr="disabled"
                                style="background-color: #2563eb; color: #ffffff;"
                                class="mu-btn-sm shadow-2xs hover:opacity-90 active:scale-95 disabled:opacity-70"
                            >
                                <x-filament::icon icon="heroicon-m-check" class="h-3.5 w-3.5 text-white" />
                                <span wire:loading.remove wire:target="saveUnit">Simpan Perubahan</span>
                                <span wire:loading wire:target="saveUnit">Menyimpan...</span>
                            </button>
                        @else
                            {{-- Reset & Simpan --}}
                            <button
                                type="button"
                                wire:click="initUnitForm"
                                class="mu-btn-sm border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                            >
                                <x-filament::icon icon="heroicon-m-arrow-path" class="h-3.5 w-3.5 text-gray-500" />
                                <span>Reset</span>
                            </button>

                            <button
                                type="button"
                                wire:click="saveUnit"
                                wire:loading.attr="disabled"
                                style="background-color: #2563eb; color: #ffffff;"
                                class="mu-btn-sm shadow-2xs hover:opacity-90 active:scale-95 disabled:opacity-70"
                            >
                                <x-filament::icon icon="heroicon-m-check" class="h-3.5 w-3.5 text-white" />
                                <span wire:loading.remove wire:target="saveUnit">Simpan Data Unit</span>
                                <span wire:loading wire:target="saveUnit">Menyimpan...</span>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Form Inputs Card Atas: 1 Baris Ultra-Compact pada Desktop --}}
                <style>
                    @media (min-width: 1024px) {
                        .mu-top-form-grid {
                            display: grid !important;
                            grid-template-columns: minmax(200px, 3fr) 100px 140px minmax(180px, 2fr) auto !important;
                            gap: 0.75rem !important;
                            align-items: end !important;
                        }
                    }
                </style>
                <div class="mu-top-form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 0.75rem; align-items: end;">
                    {{-- 1. Nama Unit --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-950 dark:text-white mb-1">
                            Nama Unit / Lab <span style="color: #dc2626; font-weight: 700;">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model="unitForm.nama"
                            placeholder="Contoh: Teknik Elektronika"
                            class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 shadow-2xs placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        />
                        @error('unitForm.nama')
                            <p class="mt-0.5 text-[10px] text-red-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 2. Kode Unit --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-950 dark:text-white mb-1">
                            Kode <span style="color: #dc2626; font-weight: 700;">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model="unitForm.kode"
                            placeholder="TE"
                            style="text-transform: uppercase;"
                            class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-mono font-bold text-gray-900 shadow-2xs placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        />
                        @error('unitForm.kode')
                            <p class="mt-0.5 text-[10px] text-red-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 3. Tipe Unit --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-950 dark:text-white mb-1">
                            Tipe <span style="color: #dc2626; font-weight: 700;">*</span>
                        </label>
                        <select
                            wire:model="unitForm.tipe"
                            class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 shadow-2xs focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer"
                        >
                            @foreach ($this->tipeOptions as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 4. Kepala Lab / PJ --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-950 dark:text-white mb-1">
                            Kepala Lab / PJ
                        </label>
                        <input
                            type="text"
                            wire:model="unitForm.kepala_nama"
                            placeholder="Nama penanggung jawab"
                            class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 shadow-2xs placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        />
                    </div>

                    {{-- 5. Status Aktif --}}
                    <div style="padding-bottom: 2px;">
                        <label class="inline-flex items-center gap-1.5 cursor-pointer select-none rounded-lg border border-gray-200 dark:border-gray-700 px-2.5 py-1.5 bg-gray-50 dark:bg-gray-800 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 transition whitespace-nowrap">
                            <input
                                type="checkbox"
                                wire:model="unitForm.aktif"
                                class="h-3.5 w-3.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                            />
                            <span>Aktif</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════════════════════ --}}
        {{-- LAYOUT 2 KOLOM: HEADER & SEARCH TETAP, HANYA ISI CARD YANG BERGESER (SCROLLABLE)   --}}
        {{-- ══════════════════════════════════════════════════════════════════════════════════ --}}
        <div class="mu-split-grid">

            {{-- ────────────────────────────────────────────────────────────────────────── --}}
            {{-- KOLOM KIRI: DAFTAR UNIT & LAB                                              --}}
            {{-- ────────────────────────────────────────────────────────────────────────── --}}
            <div class="mu-column-card">
                {{-- Bagian Atas Tetap (TIDAK TERGESER): Header & Kotak Pencarian --}}
                <div class="mu-column-fixed-header">
                    {{-- Baris 1: Header Kolom Kiri --}}
                    <div class="mu-panel-header" style="padding-bottom: 0.625rem; margin-bottom: 0.625rem;">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400">
                                <x-filament::icon icon="heroicon-o-building-office-2" class="h-4 w-4" />
                            </div>
                            <h3 class="text-sm font-extrabold uppercase tracking-wider text-gray-900 dark:text-white truncate">
                                Daftar Unit & Lab
                            </h3>
                            <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-bold text-blue-800 dark:bg-blue-900/60 dark:text-blue-300">
                                {{ $this->units->count() }}
                            </span>
                        </div>

                        @if ($selectedUnitId)
                            <button
                                type="button"
                                wire:click="startCreateUnit"
                                class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:text-blue-800 dark:text-blue-400 cursor-pointer shrink-0"
                                title="Buat unit baru"
                            >
                                <x-filament::icon icon="heroicon-m-plus" class="h-3.5 w-3.5" />
                                <span>Tambah Unit</span>
                            </button>
                        @endif
                    </div>

                    {{-- Baris 2: Kotak Pencarian Unit (TETAP / TIDAK TERGESER) --}}
                    <div class="relative flex items-center" style="height: 38px; margin-bottom: 0.875rem;">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <x-filament::icon icon="heroicon-m-magnifying-glass" class="h-4 w-4 text-gray-400" />
                        </div>
                        <input
                            type="text"
                            wire:model.live.debounce.250ms="searchUnit"
                            placeholder="Cari kode atau nama unit..."
                            class="w-full h-full rounded-xl border border-gray-200/90 bg-gray-50/50 pl-9 pr-3 text-xs text-gray-900 shadow-2xs placeholder:text-gray-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 dark:border-white/10 dark:bg-gray-800 dark:text-white"
                        />
                    </div>
                </div>

                {{-- Area List Card Unit (HANYA BAGIAN INI YANG BERGESER / SCROLLABLE) --}}
                <div class="mu-column-scroll-area custom-scrollbar">
                    @forelse ($this->units as $u)
                        @php
                            $isSelected = $selectedUnitId === $u->id;
                        @endphp
                        <div
                            wire:key="unit-card-{{ $u->id }}"
                            wire:click="selectUnit({{ $u->id }})"
                            class="mu-item-card {{ $isSelected ? 'mu-item-card-selected' : 'mu-item-card-unselected' }} cursor-pointer select-none"
                        >
                            {{-- Baris Atas: Kode & Status Terpilih --}}
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span
                                        style="{{ $isSelected ? 'background-color: #2563eb; color: #ffffff;' : 'background-color: #f1f5f9; color: #1e293b; border: 1px solid #cbd5e1;' }}"
                                        class="rounded-md font-mono text-xs font-extrabold px-2.5 py-0.5"
                                    >
                                        {{ $u->kode }}
                                    </span>

                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full {{
                                        $u->tipe === 'jurusan' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/50 dark:text-indigo-300' :
                                        ($u->tipe === 'gudang' ? 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/50 dark:text-amber-300' :
                                        'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300')
                                    }}">
                                        {{ ucfirst($u->tipe) }}
                                    </span>
                                </div>

                                @if ($isSelected)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-700 dark:text-blue-300 bg-blue-100/90 dark:bg-blue-900/60 px-2 py-0.5 rounded-full">
                                        <x-filament::icon icon="heroicon-m-check-circle" class="h-3.5 w-3.5 text-blue-600" />
                                        <span>Aktif Dipilih</span>
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 group-hover:text-blue-500">
                                        Klik untuk kelola &rarr;
                                    </span>
                                @endif
                            </div>

                            {{-- Nama Unit / Jurusan --}}
                            <div class="text-sm font-extrabold {{ $isSelected ? 'text-blue-950 dark:text-blue-100' : 'text-gray-900 dark:text-white' }}">
                                {{ $u->nama }}
                            </div>

                            {{-- Info Footer: Penanggung Jawab & Jumlah Ruangan --}}
                            <div class="mt-2.5 pt-2 border-t {{ $isSelected ? 'border-blue-200/80 dark:border-blue-900/60' : 'border-gray-100 dark:border-gray-800' }} flex items-center justify-between text-xs">
                                <div class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400 truncate max-w-[200px]">
                                    <x-filament::icon icon="heroicon-m-user" class="h-3.5 w-3.5 shrink-0 text-gray-400" />
                                    <span class="truncate">{{ $u->kepala_nama ?: 'Belum ada PJ' }}</span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-bold text-[11px] {{
                                        $u->rooms_count > 0
                                            ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800'
                                            : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'
                                    }}">
                                        <x-filament::icon icon="heroicon-m-squares-2x2" class="h-3 w-3" />
                                        <span>{{ $u->rooms_count }} ruang</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center dark:border-gray-700">
                            <x-filament::icon icon="heroicon-o-building-office-2" class="mx-auto h-8 w-8 text-gray-400" />
                            <p class="mt-2 text-xs font-semibold text-gray-500">Tidak ada unit yang cocok dengan pencarian.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- ────────────────────────────────────────────────────────────────────────── --}}
            {{-- KOLOM KANAN: PANEL KELOLA RUANGAN UNTUK UNIT TERPILIH                      --}}
            {{-- ────────────────────────────────────────────────────────────────────────── --}}
            <div class="mu-column-card">
                @if ($selectedUnit)
                    {{-- Bagian Atas Tetap (TIDAK TERGESER): Header Ruangan 2 Baris Sejajar dengan Kiri --}}
                    <div class="mu-column-fixed-header">
                        {{-- Baris 1: Judul Satu Baris Panjang --}}
                        <div class="mu-panel-header" style="padding-bottom: 0.625rem; margin-bottom: 0.625rem;">
                            <div class="flex items-center gap-2 min-w-0 w-full">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400">
                                    <x-filament::icon icon="heroicon-o-squares-2x2" class="h-4 w-4" />
                                </div>
                                <h3 class="text-sm font-extrabold uppercase tracking-wider text-gray-900 dark:text-white truncate">
                                    Ruangan: <span class="text-blue-600 dark:text-blue-400">{{ $selectedUnit->nama }}</span>
                                </h3>
                            </div>
                        </div>

                        {{-- Baris 2: Di Bawahnya ada Jumlah Ruang (Kiri) & Tambah Ruang (Kanan) --}}
                        <div class="flex items-center justify-between gap-2" style="height: 38px; margin-bottom: 0.875rem;">
                            {{-- Kiri: Badge Jumlah Ruang --}}
                            <div class="flex items-center gap-1.5">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-3 py-1.5 text-xs font-bold text-blue-800 dark:bg-blue-900/60 dark:text-blue-300">
                                    <x-filament::icon icon="heroicon-m-squares-2x2" class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400" />
                                    <span>{{ $selectedUnit->rooms->count() + count($newRooms) }} Ruang</span>
                                </span>
                            </div>

                            {{-- Kanan: Tombol "+ Tambah Ruang" --}}
                            <button
                                type="button"
                                wire:click="addNewRoomCard"
                                style="background-color: #2563eb; color: #ffffff;"
                                class="inline-flex items-center justify-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-bold shadow-sm hover:opacity-90 active:scale-95 cursor-pointer transition shrink-0"
                                title="Klik untuk memunculkan card penambahan ruangan"
                            >
                                <x-filament::icon icon="heroicon-m-plus" class="h-4 w-4 text-white" />
                                <span>Tambah Ruang</span>
                            </button>
                        </div>
                    </div>

                    {{-- Area Scrollable Ruangan (HANYA BAGIAN INI YANG BERGESER / SCROLLABLE) --}}
                    <div class="mu-column-scroll-area custom-scrollbar">

                        {{-- ═══ SECTION A: DAFTAR CARD RUANGAN BARU YANG SEDANG DITAMBAHKAN ═══ --}}
                        @if (count($newRooms) > 0)
                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                @foreach ($newRooms as $idx => $nRoom)
                                    <div
                                        wire:key="new-room-card-{{ $nRoom['temp_id'] }}"
                                        class="mu-new-room-card"
                                    >
                                        {{-- Header Card Baru + Tanda Silang (✕) --}}
                                        <div class="flex items-center justify-between pb-2 mb-2.5 border-b border-blue-200/80 dark:border-blue-900/60">
                                            <div class="flex items-center gap-2">
                                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-white font-extrabold text-[11px]">
                                                    {{ $idx + 1 }}
                                                </span>
                                                <span class="text-xs font-bold uppercase tracking-wider text-blue-900 dark:text-blue-200">
                                                    Tambah Ruangan Baru #{{ $idx + 1 }}
                                                </span>
                                            </div>

                                            <button
                                                type="button"
                                                wire:click="removeNewRoomCard({{ $idx }})"
                                                class="inline-flex items-center justify-center p-1 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/50 transition cursor-pointer"
                                                title="Batalkan / Hapus Card Ruangan Ini"
                                            >
                                                <x-filament::icon icon="heroicon-m-x-mark" class="h-4 w-4 text-gray-500 hover:text-red-600" />
                                            </button>
                                        </div>

                                        {{-- Inputs Card Ruangan Baru: Compact Horizontal Grid --}}
                                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)) 90px auto; gap: 0.75rem; align-items: end;">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-950 dark:text-white mb-1">
                                                    Nama Ruangan <span style="color: #dc2626; font-weight: 700;">*</span>
                                                </label>
                                                <input
                                                    type="text"
                                                    wire:model="newRooms.{{ $idx }}.nama"
                                                    placeholder="Contoh: Lab Komputer 1 / Bengkel"
                                                    class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 shadow-2xs placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                />
                                            </div>

                                            <div>
                                                <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 mb-1">
                                                    Penanggung Jawab
                                                </label>
                                                <input
                                                    type="text"
                                                    wire:model="newRooms.{{ $idx }}.penanggung_jawab"
                                                    placeholder="Nama PJ Ruangan"
                                                    class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 shadow-2xs placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                />
                                            </div>

                                            <div>
                                                <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 mb-1">
                                                    Kapasitas
                                                </label>
                                                <input
                                                    type="number"
                                                    min="0"
                                                    wire:model="newRooms.{{ $idx }}.kapasitas"
                                                    placeholder="36"
                                                    class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 shadow-2xs placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                />
                                            </div>

                                            <div class="flex items-center gap-1.5" style="padding-bottom: 2px;">
                                                <label class="inline-flex items-center gap-1.5 cursor-pointer rounded-lg border border-gray-200 dark:border-gray-700 px-2.5 py-1.5 bg-white dark:bg-gray-800 text-xs font-bold text-gray-700 dark:text-gray-300">
                                                    <input
                                                        type="checkbox"
                                                        wire:model="newRooms.{{ $idx }}.aktif"
                                                        class="h-3.5 w-3.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                                    />
                                                    <span>Aktif</span>
                                                </label>

                                                <button
                                                    type="button"
                                                    wire:click="saveNewRoom({{ $idx }})"
                                                    style="background-color: #059669; color: #ffffff;"
                                                    class="inline-flex items-center justify-center gap-1 rounded-lg px-3.5 py-1.5 text-xs font-bold shadow-2xs hover:opacity-90 active:scale-95 cursor-pointer transition"
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

                        {{-- ═══ SECTION B: DAFTAR RUANGAN YANG SUDAH TERSIMPAN ═══ --}}
                        @forelse ($selectedUnit->rooms as $room)
                            <div
                                wire:key="existing-room-card-{{ $room->id }}"
                                class="mu-item-card mu-item-card-unselected"
                            >
                                @if ($editingRoomId === $room->id)
                                    {{-- Form Edit Inline Ruangan --}}
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-800">
                                            <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">
                                                Edit Ruangan: {{ $room->nama }}
                                            </span>
                                            <button
                                                type="button"
                                                wire:click="cancelEditRoom"
                                                class="text-xs font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer"
                                            >
                                                Batal
                                            </button>
                                        </div>

                                        {{-- Edit Ruangan: Compact Horizontal Grid --}}
                                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)) 90px auto; gap: 0.75rem; align-items: end;">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-950 dark:text-white mb-1">
                                                    Nama Ruangan <span style="color: #dc2626; font-weight: 700;">*</span>
                                                </label>
                                                <input
                                                    type="text"
                                                    wire:model="editingRoomData.nama"
                                                    class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                />
                                            </div>

                                            <div>
                                                <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 mb-1">Penanggung Jawab</label>
                                                <input
                                                    type="text"
                                                    wire:model="editingRoomData.penanggung_jawab"
                                                    placeholder="Nama PJ"
                                                    class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                />
                                            </div>

                                            <div>
                                                <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 mb-1">Kapasitas</label>
                                                <input
                                                    type="number"
                                                    min="0"
                                                    wire:model="editingRoomData.kapasitas"
                                                    placeholder="36"
                                                    class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                />
                                            </div>

                                            <div class="flex items-center gap-1.5" style="padding-bottom: 2px;">
                                                <label class="inline-flex items-center gap-1.5 cursor-pointer rounded-lg border border-gray-200 dark:border-gray-700 px-2.5 py-1.5 bg-white dark:bg-gray-800 text-xs font-bold text-gray-700 dark:text-gray-300">
                                                    <input
                                                        type="checkbox"
                                                        wire:model="editingRoomData.aktif"
                                                        class="h-3.5 w-3.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                                    />
                                                    <span>Aktif</span>
                                                </label>

                                                <button
                                                    type="button"
                                                    wire:click="updateRoom({{ $room->id }})"
                                                    style="background-color: #2563eb; color: #ffffff;"
                                                    class="inline-flex items-center justify-center gap-1 rounded-lg px-3.5 py-1.5 text-xs font-bold shadow-2xs hover:opacity-90 cursor-pointer transition"
                                                >
                                                    <x-filament::icon icon="heroicon-m-check" class="h-3.5 w-3.5 text-white" />
                                                    <span>Simpan</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    {{-- Tampilan Normal Card Ruangan --}}
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-start gap-3 min-w-0">
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600 dark:bg-gray-800 dark:text-gray-300 mt-0.5">
                                                <x-filament::icon icon="heroicon-o-home-modern" class="h-4 w-4" />
                                            </div>

                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <h4 class="text-sm font-extrabold text-gray-900 dark:text-white truncate">
                                                        {{ $room->nama }}
                                                    </h4>

                                                    @if ($room->kode)
                                                        <span class="rounded bg-gray-100 px-2 py-0.5 font-mono text-[10px] font-bold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                                            {{ $room->kode }}
                                                        </span>
                                                    @endif

                                                    @if ($room->aktif)
                                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800">
                                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                            <span>Aktif</span>
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-bold text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                                            <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                                                            <span>Nonaktif</span>
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="mt-1 flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400 flex-wrap">
                                                    <div class="flex items-center gap-1">
                                                        <x-filament::icon icon="heroicon-m-user" class="h-3.5 w-3.5 text-gray-400" />
                                                        <span>PJ: <strong class="text-gray-700 dark:text-gray-200">{{ $room->penanggung_jawab ?: '—' }}</strong></span>
                                                    </div>

                                                    @if ($room->kapasitas)
                                                        <div class="flex items-center gap-1">
                                                            <x-filament::icon icon="heroicon-m-users" class="h-3.5 w-3.5 text-gray-400" />
                                                            <span>{{ $room->kapasitas }} siswa</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Aksi Edit & Hapus Ruangan --}}
                                        <div class="flex items-center gap-1.5 shrink-0">
                                            <button
                                                type="button"
                                                wire:click="editRoom({{ $room->id }})"
                                                class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:text-blue-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 cursor-pointer transition"
                                                title="Ubah Ruangan"
                                            >
                                                <x-filament::icon icon="heroicon-m-pencil-square" class="h-3.5 w-3.5 text-blue-500" />
                                                <span>Ubah</span>
                                            </button>

                                            <button
                                                type="button"
                                                wire:confirm="Hapus ruangan '{{ $room->nama }}'?"
                                                wire:click="deleteRoom({{ $room->id }})"
                                                class="inline-flex items-center justify-center p-1 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/50 cursor-pointer transition"
                                                title="Hapus Ruangan"
                                            >
                                                <x-filament::icon icon="heroicon-m-trash" class="h-3.5 w-3.5 text-red-500" />
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @empty
                            @if (count($newRooms) === 0)
                                <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
                                    <x-filament::icon icon="heroicon-o-squares-plus" class="mx-auto h-8 w-8 text-gray-400" />
                                    <h4 class="mt-2 text-sm font-bold text-gray-800 dark:text-gray-200">
                                        Belum Ada Ruangan di {{ $selectedUnit->nama }}
                                    </h4>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Klik tombol Tambah Ruang di atas untuk menambahkan ruangan ke unit ini.
                                    </p>
                                </div>
                            @endif
                        @endforelse
                    </div>

                @else
                    {{-- Kondisi Belum Ada Unit yang Dipilih --}}
                    <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 bg-gray-50/50 p-12 text-center shadow-xs dark:border-white/10 dark:bg-gray-900/50 h-full">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400">
                            <x-filament::icon icon="heroicon-o-cursor-arrow-rays" class="h-7 w-7" />
                        </div>
                        <h4 class="mt-4 text-base font-extrabold text-gray-900 dark:text-white">
                            Pilih Unit / Lab di Sebelah Kiri
                        </h4>
                        <p class="mt-1.5 max-w-sm text-xs text-gray-500 dark:text-gray-400">
                            Klik salah satu card Unit / Jurusan di sebelah kiri untuk melihat daftar ruangan, menambah ruangan baru, atau mengedit data unit tersebut.
                        </p>
                    </div>
                @endif
            </div>

        </div>

    </div>
</x-filament-panels::page>
