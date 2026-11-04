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

        /* ─── 1. AREA ATAS TETAP (STICKY): JUDUL HALAMAN & FORM CREATE/EDIT KATEGORI ─── */
        .mu-sticky-top-block {
            position: sticky !important;
            top: 4rem !important;
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

        /* Form Card Tambah Kategori Baru */
        .mu-new-category-card {
            border: 2px dashed #60a5fa;
            background-color: #f0f7ff;
            border-radius: 1rem;
            padding: 1.125rem 1.25rem !important;
            flex-shrink: 0 !important;
        }
        .dark .mu-new-category-card {
            border-color: #3b82f6;
            background-color: rgba(30, 58, 138, 0.2);
        }

        /* Custom Scrollbar */
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
        {{-- AREA ATAS TETAP (STICKY): JUDUL HALAMAN & CARD CREATE/EDIT KATEGORI                --}}
        {{-- ══════════════════════════════════════════════════════════════════════════════════ --}}
        <div class="mu-sticky-top-block">
            {{-- Judul Halaman Tetap --}}
            <div style="margin-bottom: 0.75rem;">
                <h1 class="fi-header-heading text-2xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl">
                    Monitoring Kategori (Seluruh Unit)
                </h1>
            </div>

            {{-- Card Form Edit / Create Kategori (Ultra-Compact) --}}
            <div class="mu-top-card">
                {{-- Header Card Atas --}}
                <div class="mu-top-header">
                    {{-- Judul Ringkas di Kiri --}}
                    <div class="flex items-center gap-2 min-w-0">
                        @if (! empty($categoryForm['id']))
                            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 shrink-0">
                                <x-filament::icon icon="heroicon-o-pencil-square" class="h-4 w-4" />
                            </div>
                            <div class="flex items-center truncate">
                                <h2 class="text-xs sm:text-sm font-extrabold text-gray-900 dark:text-white truncate">
                                    Edit Kategori: <span class="text-blue-600 dark:text-blue-400">{{ $categoryForm['nama'] ?: 'Kategori' }}</span>
                                </h2>
                            </div>
                        @else
                            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 shrink-0">
                                <x-filament::icon icon="heroicon-o-plus-circle" class="h-4 w-4" />
                            </div>
                            <h2 class="text-xs sm:text-sm font-extrabold text-gray-900 dark:text-white truncate">
                                Tambah Kategori Baru
                            </h2>
                        @endif
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center gap-1.5 shrink-0" style="margin-left: auto;">
                        @if (! empty($categoryForm['id']))
                            {{-- 1. Tambah Kategori Baru --}}
                            <button
                                type="button"
                                wire:click="startCreateCategory"
                                class="mu-btn-sm border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                                title="Beralih ke form buat kategori baru"
                            >
                                <x-filament::icon icon="heroicon-m-plus" class="h-3.5 w-3.5 text-emerald-600" />
                                <span>Tambah Kategori Baru</span>
                            </button>

                            {{-- 2. Hapus Kategori --}}
                            <button
                                type="button"
                                wire:confirm="Yakin ingin menghapus kategori ini? Data barang yang menggunakan kategori ini tidak akan terhapus."
                                wire:click="deleteCategoryFromTop"
                                class="mu-btn-sm border border-red-200 bg-red-50/80 text-red-600 hover:bg-red-100 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-400"
                                title="Hapus kategori ini"
                            >
                                <x-filament::icon icon="heroicon-m-trash" class="h-3.5 w-3.5 text-red-500" />
                                <span>Hapus Kategori</span>
                            </button>

                            {{-- 3. Simpan Perubahan --}}
                            <button
                                type="button"
                                wire:click="saveCategory"
                                wire:loading.attr="disabled"
                                style="background-color: #2563eb; color: #ffffff;"
                                class="mu-btn-sm shadow-2xs hover:opacity-90 active:scale-95 disabled:opacity-70"
                            >
                                <x-filament::icon icon="heroicon-m-check" class="h-3.5 w-3.5 text-white" />
                                <span wire:loading.remove wire:target="saveCategory">Simpan Perubahan</span>
                                <span wire:loading wire:target="saveCategory">Menyimpan...</span>
                            </button>
                        @else
                            {{-- Reset & Simpan --}}
                            <button
                                type="button"
                                wire:click="initCategoryForm"
                                class="mu-btn-sm border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                            >
                                <x-filament::icon icon="heroicon-m-arrow-path" class="h-3.5 w-3.5 text-gray-500" />
                                <span>Reset</span>
                            </button>

                            <button
                                type="button"
                                wire:click="saveCategory"
                                wire:loading.attr="disabled"
                                style="background-color: #2563eb; color: #ffffff;"
                                class="mu-btn-sm shadow-2xs hover:opacity-90 active:scale-95 disabled:opacity-70"
                            >
                                <x-filament::icon icon="heroicon-m-check" class="h-3.5 w-3.5 text-white" />
                                <span wire:loading.remove wire:target="saveCategory">Simpan Data Kategori</span>
                                <span wire:loading wire:target="saveCategory">Menyimpan...</span>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Form Inputs Card Atas: 1 Baris Ultra-Compact pada Desktop --}}
                <style>
                    @media (min-width: 1024px) {
                        .mc-top-form-grid {
                            display: grid !important;
                            grid-template-columns: minmax(220px, 3fr) minmax(200px, 2.5fr) 110px !important;
                            gap: 0.75rem !important;
                            align-items: end !important;
                        }
                    }
                </style>
                <div class="mc-top-form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem; align-items: end;">
                    {{-- 1. Nama Kategori --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-950 dark:text-white mb-1">
                            Nama Kategori <span style="color: #dc2626; font-weight: 700;">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model="categoryForm.nama"
                            placeholder="Contoh: Alat Ukur & Kalibrasi"
                            class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 shadow-2xs placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        />
                        @error('categoryForm.nama')
                            <p class="mt-0.5 text-[10px] text-red-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 2. Jurusan / Unit --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-950 dark:text-white mb-1">
                            Jurusan / Unit <span style="color: #dc2626; font-weight: 700;">*</span>
                        </label>
                        <select
                            wire:model="categoryForm.unit_id"
                            class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 shadow-2xs focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer"
                        >
                            @foreach ($this->unitOptions as $uId => $uNama)
                                <option value="{{ $uId }}">{{ $uNama }}</option>
                            @endforeach
                        </select>
                        @error('categoryForm.unit_id')
                            <p class="mt-0.5 text-[10px] text-red-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 3. Urutan Tampil --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-950 dark:text-white mb-1">
                            Urutan Tampil
                        </label>
                        <input
                            type="number"
                            min="0"
                            wire:model="categoryForm.urutan"
                            placeholder="0"
                            class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 shadow-2xs placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        />
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════════════════════ --}}
        {{-- LAYOUT 2 KOLOM: HEADER & SEARCH TETAP, HANYA ISI CARD YANG BERGESER (SCROLLABLE)   --}}
        {{-- ══════════════════════════════════════════════════════════════════════════════════ --}}
        <div class="mu-split-grid">

            {{-- ────────────────────────────────────────────────────────────────────────── --}}
            {{-- KOLOM KIRI (CARD 1): DAFTAR JURUSAN / UNIT (MASTER)                        --}}
            {{-- ────────────────────────────────────────────────────────────────────────── --}}
            <div class="mu-column-card">
                {{-- Bagian Atas Tetap: Header & Kotak Pencarian --}}
                <div class="mu-column-fixed-header">
                    {{-- Baris 1: Header Kolom Kiri --}}
                    <div class="mu-panel-header" style="padding-bottom: 0.625rem; margin-bottom: 0.625rem;">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400">
                                <x-filament::icon icon="heroicon-o-building-office-2" class="h-4 w-4" />
                            </div>
                            <h3 class="text-sm font-extrabold uppercase tracking-wider text-gray-900 dark:text-white truncate">
                                Jurusan / Unit
                            </h3>
                            <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-bold text-blue-800 dark:bg-blue-900/60 dark:text-blue-300">
                                {{ $this->units->count() }}
                            </span>
                        </div>
                    </div>

                    {{-- Baris 2: Kotak Pencarian Unit (TETAP / TIDAK TERGESER) --}}
                    <div class="relative flex items-center" style="height: 38px; margin-bottom: 0.875rem;">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <x-filament::icon icon="heroicon-m-magnifying-glass" class="h-4 w-4 text-gray-400" />
                        </div>
                        <input
                            type="text"
                            wire:model.live.debounce.250ms="searchUnit"
                            placeholder="Cari kode atau nama jurusan/unit..."
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
                                <div class="flex items-center gap-1.5 flex-wrap">
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
                                        Pilih Unit &rarr;
                                    </span>
                                @endif
                            </div>

                            {{-- Nama Unit / Jurusan --}}
                            <div class="text-sm font-extrabold {{ $isSelected ? 'text-blue-950 dark:text-blue-100' : 'text-gray-900 dark:text-white' }}">
                                {{ $u->nama }}
                            </div>

                            {{-- Info Footer: Jumlah Kategori & Jumlah Barang --}}
                            <div class="mt-2.5 pt-2 border-t {{ $isSelected ? 'border-blue-200/80 dark:border-blue-900/60' : 'border-gray-100 dark:border-gray-800' }} flex items-center justify-between text-xs">
                                <div class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400 truncate max-w-[190px]">
                                    <x-filament::icon icon="heroicon-m-user" class="h-3.5 w-3.5 shrink-0 text-gray-400" />
                                    <span class="truncate">{{ $u->kepala_nama ?: 'Belum ada PJ' }}</span>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-bold text-[11px] {{
                                        $u->categories_count > 0
                                            ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800'
                                            : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'
                                    }}">
                                        <x-filament::icon icon="heroicon-m-tag" class="h-3 w-3" />
                                        <span>{{ $u->categories_count }} kategori</span>
                                    </span>

                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-bold text-[11px] bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                        <span>{{ $u->items_count }} item</span>
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
            {{-- KOLOM KANAN (CARD 2): PANEL KELOLA KATEGORI BARANG (DETAIL)                --}}
            {{-- ────────────────────────────────────────────────────────────────────────── --}}
            <div class="mu-column-card">
                @if ($selectedUnit)
                    {{-- Bagian Atas Tetap: Header Kategori --}}
                    <div class="mu-column-fixed-header">
                        {{-- Baris 1: Judul Unit Terpilih --}}
                        <div class="mu-panel-header" style="padding-bottom: 0.625rem; margin-bottom: 0.625rem;">
                            <div class="flex items-center gap-2 min-w-0 w-full">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400">
                                    <x-filament::icon icon="heroicon-o-tag" class="h-4 w-4" />
                                </div>
                                <h3 class="text-sm font-extrabold uppercase tracking-wider text-gray-900 dark:text-white truncate">
                                    Kategori: <span class="text-blue-600 dark:text-blue-400">{{ $selectedUnit->nama }}</span>
                                    <span class="text-xs font-normal text-gray-500 dark:text-gray-400 ml-1">({{ $selectedUnit->kode }})</span>
                                </h3>
                            </div>
                        </div>

                        {{-- Baris 2: Badge Jumlah Kategori (Kiri) & Tambah Kategori (Kanan) --}}
                        <div class="flex items-center justify-between gap-2" style="height: 38px; margin-bottom: 0.875rem;">
                            {{-- Kiri: Badge Jumlah Kategori --}}
                            <div class="flex items-center gap-1.5">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-3 py-1.5 text-xs font-bold text-blue-800 dark:bg-blue-900/60 dark:text-blue-300">
                                    <x-filament::icon icon="heroicon-m-tag" class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400" />
                                    <span>{{ $selectedUnit->categories->count() + count($newCategories) }} Kategori</span>
                                </span>
                            </div>

                            {{-- Kanan: Tombol "+ Tambah Kategori" --}}
                            <button
                                type="button"
                                wire:click="addNewCategoryCard"
                                style="background-color: #2563eb; color: #ffffff;"
                                class="inline-flex items-center justify-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-bold shadow-sm hover:opacity-90 active:scale-95 cursor-pointer transition shrink-0"
                                title="Klik untuk memunculkan form penambahan kategori baru"
                            >
                                <x-filament::icon icon="heroicon-m-plus" class="h-4 w-4 text-white" />
                                <span>Tambah Kategori</span>
                            </button>
                        </div>
                    </div>

                    {{-- Area Scrollable Kategori (HANYA BAGIAN INI YANG BERGESER) --}}
                    <div class="mu-column-scroll-area custom-scrollbar">

                        {{-- ═══ SECTION A: DAFTAR CARD KATEGORI BARU YANG SEDANG DITAMBAHKAN ═══ --}}
                        @if (count($newCategories) > 0)
                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                @foreach ($newCategories as $idx => $nCat)
                                    <div
                                        wire:key="new-category-card-{{ $nCat['temp_id'] }}"
                                        class="mu-new-category-card"
                                    >
                                        {{-- Header Card Baru + Tanda Silang (✕) --}}
                                        <div class="flex items-center justify-between pb-2 mb-2.5 border-b border-blue-200/80 dark:border-blue-900/60">
                                            <div class="flex items-center gap-2">
                                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-white font-extrabold text-[11px]">
                                                    {{ $idx + 1 }}
                                                </span>
                                                <span class="text-xs font-bold uppercase tracking-wider text-blue-900 dark:text-blue-200">
                                                    Tambah Kategori Baru #{{ $idx + 1 }}
                                                </span>
                                            </div>

                                            <button
                                                type="button"
                                                wire:click="removeNewCategoryCard({{ $idx }})"
                                                class="inline-flex items-center justify-center p-1 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/50 transition cursor-pointer"
                                                title="Batalkan / Hapus Card Ini"
                                            >
                                                <x-filament::icon icon="heroicon-m-x-mark" class="h-4 w-4 text-gray-500 hover:text-red-600" />
                                            </button>
                                        </div>

                                        {{-- Inputs Card Kategori Baru: Compact Horizontal Grid --}}
                                        <div style="display: grid; grid-template-columns: minmax(180px, 3fr) 90px auto; gap: 0.75rem; align-items: end;">
                                            {{-- Nama Kategori --}}
                                            <div>
                                                <label class="block text-xs font-medium text-gray-950 dark:text-white mb-1">
                                                    Nama Kategori <span style="color: #dc2626; font-weight: 700;">*</span>
                                                </label>
                                                <input
                                                    type="text"
                                                    wire:model="newCategories.{{ $idx }}.nama"
                                                    placeholder="Contoh: Alat Ukur & Kalibrasi..."
                                                    class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 shadow-2xs placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                />
                                            </div>

                                            {{-- Urutan Tampil --}}
                                            <div>
                                                <label class="block text-xs font-medium text-gray-950 dark:text-white mb-1">
                                                    Urutan
                                                </label>
                                                <input
                                                    type="number"
                                                    min="0"
                                                    wire:model="newCategories.{{ $idx }}.urutan"
                                                    placeholder="0"
                                                    class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 shadow-2xs placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                />
                                            </div>

                                            {{-- Tombol Simpan --}}
                                            <div style="padding-bottom: 2px;">
                                                <button
                                                    type="button"
                                                    wire:click="saveNewCategory({{ $idx }})"
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

                        {{-- ═══ SECTION B: DAFTAR KATEGORI YANG SUDAH TERSIMPAN ═══ --}}
                        @forelse ($selectedUnit->categories as $category)
                            <div
                                wire:key="existing-cat-card-{{ $category->id }}"
                                class="mu-item-card mu-item-card-unselected"
                            >
                                @if ($editingCategoryId === $category->id)
                                    {{-- Form Edit Inline Kategori --}}
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-800">
                                            <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">
                                                Edit Kategori: {{ $category->nama }}
                                            </span>
                                            <button
                                                type="button"
                                                wire:click="cancelEditCategory"
                                                class="text-xs font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 cursor-pointer"
                                            >
                                                Batal
                                            </button>
                                        </div>

                                        <div style="display: grid; grid-template-columns: minmax(180px, 3fr) 90px auto; gap: 0.75rem; align-items: end;">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-950 dark:text-white mb-1">
                                                    Nama Kategori <span style="color: #dc2626; font-weight: 700;">*</span>
                                                </label>
                                                <input
                                                    type="text"
                                                    wire:model="editingCategoryData.nama"
                                                    class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                />
                                            </div>

                                            <div>
                                                <label class="block text-xs font-medium text-gray-950 dark:text-white mb-1">Urutan</label>
                                                <input
                                                    type="number"
                                                    min="0"
                                                    wire:model="editingCategoryData.urutan"
                                                    class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                />
                                            </div>

                                            <div style="padding-bottom: 2px;">
                                                <button
                                                    type="button"
                                                    wire:click="updateCategory({{ $category->id }})"
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
                                    {{-- Tampilan Normal Card Kategori --}}
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                            {{-- Badge Urutan --}}
                                            <span class="rounded-md font-mono text-xs font-extrabold px-2 py-0.5 bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700 shrink-0">
                                                #{{ $category->urutan }}
                                            </span>

                                            {{-- Nama Kategori --}}
                                            <div class="text-sm font-bold text-gray-900 dark:text-white truncate">
                                                {{ $category->nama }}
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0">
                                            {{-- Badge Jumlah Barang --}}
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-bold text-[11px] {{
                                                $category->items_count > 0
                                                    ? 'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800'
                                                    : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'
                                            }}">
                                                <x-filament::icon icon="heroicon-m-cube" class="h-3 w-3" />
                                                <span>{{ $category->items_count }} item</span>
                                            </span>

                                            {{-- Tombol Aksi: Edit & Hapus --}}
                                            <button
                                                type="button"
                                                wire:click="editCategory({{ $category->id }})"
                                                class="inline-flex items-center justify-center p-1.5 rounded-lg text-gray-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/50 transition cursor-pointer"
                                                title="Edit Kategori Ini"
                                            >
                                                <x-filament::icon icon="heroicon-m-pencil-square" class="h-4 w-4" />
                                            </button>

                                            <button
                                                type="button"
                                                wire:confirm="Yakin ingin menghapus kategori '{{ $category->nama }}'? Data barang tidak akan terhapus."
                                                wire:click="deleteCategory({{ $category->id }})"
                                                class="inline-flex items-center justify-center p-1.5 rounded-lg text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/50 transition cursor-pointer"
                                                title="Hapus Kategori Ini"
                                            >
                                                <x-filament::icon icon="heroicon-m-trash" class="h-4 w-4 text-red-500" />
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @empty
                            @if (count($newCategories) === 0)
                                <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center dark:border-gray-700">
                                    <div class="flex h-12 w-12 mx-auto items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400">
                                        <x-filament::icon icon="heroicon-o-tag" class="h-6 w-6" />
                                    </div>
                                    <h4 class="mt-3 text-sm font-bold text-gray-900 dark:text-white">Belum Ada Kategori Barang</h4>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Unit <strong>{{ $selectedUnit->nama }}</strong> belum memiliki kategori barang.
                                    </p>
                                    <button
                                        type="button"
                                        wire:click="addNewCategoryCard"
                                        style="background-color: #2563eb; color: #ffffff;"
                                        class="mt-4 inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs font-bold shadow-sm hover:opacity-90 active:scale-95 cursor-pointer transition"
                                    >
                                        <x-filament::icon icon="heroicon-m-plus" class="h-4 w-4 text-white" />
                                        <span>Tambah Kategori Pertama</span>
                                    </button>
                                </div>
                            @endif
                        @endforelse
                    </div>
                @else
                    {{-- Empty State Jika Tidak Ada Unit yang Dipilih --}}
                    <div class="flex flex-col items-center justify-center h-full text-center p-8">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800">
                            <x-filament::icon icon="heroicon-o-building-office-2" class="h-7 w-7" />
                        </div>
                        <h4 class="mt-3 text-sm font-bold text-gray-900 dark:text-white">Pilih Jurusan / Unit di Sebelah Kiri</h4>
                        <p class="mt-1 text-xs text-gray-500 max-w-sm">
                            Pilih salah satu jurusan atau unit di kolom kiri untuk melihat, menambah, dan mengelola kategori barangnya.
                        </p>
                    </div>
                @endif
            </div>

        </div>

    </div>
</x-filament-panels::page>
