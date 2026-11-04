<x-filament-panels::page>

    <style>
        /* Samakan persis padding & gap bawaan Filament page */
        .fi-page section {
            padding-top: 0.25rem !important;
            padding-bottom: 0.5rem !important;
            gap: 0.75rem !important;
        }

        .fi-header {
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }

        /* Card Form Create Ultra-Compact */
        .inline-manage-top-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 0.875rem 1.25rem !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }
        .dark .inline-manage-top-card {
            background-color: #111827;
            border-color: rgba(255, 255, 255, 0.1);
        }

        .inline-manage-top-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 0.625rem !important;
            margin-bottom: 0.75rem !important;
            border-bottom: 1px solid #f1f5f9;
        }
        .dark .inline-manage-top-header {
            border-bottom-color: #1f2937;
        }

        .inline-manage-btn-sm {
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

        .inline-manage-top-card .fi-grid {
            gap: 0.75rem !important;
            align-items: end !important;
        }

        .inline-manage-top-card .fi-fo-field-wrp-label {
            margin-bottom: 0.25rem !important;
        }
        .inline-manage-top-card .fi-fo-field-wrp-label span {
            font-size: 0.75rem !important;
            line-height: 1rem !important;
            font-weight: 500 !important;
            color: #030712 !important;
        }
        .dark .inline-manage-top-card .fi-fo-field-wrp-label span {
            color: #ffffff !important;
        }
        .inline-manage-top-card .fi-fo-field-wrp-label sup,
        .inline-manage-top-card .fi-fo-field-wrp-label .text-danger-600 {
            color: #dc2626 !important;
            font-weight: 700 !important;
        }

        .inline-manage-top-card .fi-input-wrp {
            border-radius: 0.5rem !important;
            border: 1px solid #d1d5db !important;
            background-color: #ffffff !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
            min-height: 34px !important;
            height: 34px !important;
            --tw-ring-color: transparent !important;
            --tw-ring-offset-shadow: 0 0 #0000 !important;
            --tw-ring-shadow: 0 0 #0000 !important;
        }
        .dark .inline-manage-top-card .fi-input-wrp {
            border-color: #374151 !important;
            background-color: #1f2937 !important;
        }
        .inline-manage-top-card .fi-input-wrp:focus-within {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
        }

        .inline-manage-top-card .fi-input-wrp input,
        .inline-manage-top-card .fi-input-wrp select,
        .inline-manage-top-card .choices__inner {
            font-size: 0.75rem !important;
            line-height: 1rem !important;
            padding: 0.375rem 0.625rem !important;
            min-height: 32px !important;
            height: 32px !important;
            color: #111827 !important;
        }
        .dark .inline-manage-top-card .fi-input-wrp input,
        .dark .inline-manage-top-card .fi-input-wrp select,
        .dark .inline-manage-top-card .choices__inner {
            color: #ffffff !important;
        }
        .inline-manage-top-card input::placeholder,
        .inline-manage-top-card .choices__placeholder {
            font-size: 0.75rem !important;
            color: #9ca3af !important;
            opacity: 1 !important;
        }
    </style>

    {{-- ═══ ULTRA-COMPACT CREATE FORM SECTION ═══ --}}
    <div class="inline-manage-top-card">
        {{-- Header Card Atas --}}
        <div class="inline-manage-top-header">
            <div class="flex items-center gap-2 min-w-0">
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400 shrink-0">
                    <x-filament::icon icon="heroicon-o-plus-circle" class="h-4 w-4" />
                </div>
                <h2 class="text-xs sm:text-sm font-extrabold text-gray-900 dark:text-white truncate">
                    {{ $this->getCreateHeading() }}
                </h2>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-1.5 shrink-0" style="margin-left: auto;">
                <button
                    type="button"
                    wire:click="resetForm"
                    class="inline-manage-btn-sm border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    title="Reset Form"
                >
                    <x-filament::icon icon="heroicon-m-arrow-path" class="h-3.5 w-3.5 text-gray-500" />
                    <span>Reset</span>
                </button>

                <button
                    type="submit"
                    form="inline-create-form"
                    wire:loading.attr="disabled"
                    style="background-color: #4f46e5; color: #ffffff;"
                    class="inline-manage-btn-sm shadow-2xs hover:opacity-90 active:scale-95 disabled:opacity-70"
                    title="Simpan Data"
                >
                    <x-filament::icon icon="heroicon-m-check" class="h-3.5 w-3.5 text-white" />
                    <span wire:loading.remove wire:target="create">Simpan Data</span>
                    <span wire:loading wire:target="create">Menyimpan...</span>
                </button>
            </div>
        </div>

        {{-- Form Fields --}}
        <form wire:submit.prevent="create" id="inline-create-form">
            {{ $this->form }}
        </form>
    </div>

    {{-- ═══ BARISAN TAB KATEGORI DI ATAS TABEL (PERSIS BARANG MASUK & KELUAR) ═══ --}}
    @php
        $categories = $this->availableCategories;
        $uncategorizedCount = $this->uncategorizedItemsCount;
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
                style="{{ $isActive ? 'background-color: #4f46e5 !important; color: #ffffff !important; border-color: #4f46e5 !important;' : 'background-color: #ffffff; color: #1e293b; border-color: #cbd5e1;' }}"
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

        {{-- Tab Opsional Jika Ada Barang Belum Dikelompokkan --}}
        @if($uncategorizedCount > 0)
            @php
                $isUncategorizedActive = $selectedCategoryId === -1;
            @endphp
            <button
                type="button"
                wire:click="setCategory(-1)"
                style="{{ $isUncategorizedActive ? 'background-color: #ea580c !important; color: #ffffff !important; border-color: #ea580c !important;' : 'background-color: #fff7ed; color: #9a3412; border-color: #fed7aa;' }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all duration-200 cursor-pointer whitespace-nowrap border shadow-xs hover:opacity-90"
            >
                <x-filament::icon icon="heroicon-m-exclamation-triangle" class="w-3.5 h-3.5" style="{{ $isUncategorizedActive ? 'color: #ffffff !important;' : 'color: #ea580c;' }}" />
                <span style="{{ $isUncategorizedActive ? 'color: #ffffff !important;' : 'color: #9a3412;' }}">Belum Dikelompokkan</span>
                <span
                    style="{{ $isUncategorizedActive ? 'background-color: rgba(255, 255, 255, 0.25) !important; color: #ffffff !important;' : 'background-color: #ffedd5; color: #c2410c;' }}"
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold"
                >
                    {{ $uncategorizedCount }}
                </span>
            </button>
        @endif
    </div>

    {{-- ═══ DATA TABLE ═══ --}}
    <div>
        {{ $this->table }}
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
