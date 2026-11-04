<div class="px-3 pt-2 pb-3 mb-2 border-b border-gray-200 dark:border-gray-800">
    <div class="flex items-center justify-between mb-2">
        <span class="text-[11px] font-bold tracking-wider uppercase text-gray-500 dark:text-gray-400">
            Peran Aktif Lab
        </span>
        @if($currentRole === 'toolman')
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300 border border-amber-300 dark:border-amber-700">
                Operasional
            </span>
        @else
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-teal-100 text-teal-800 dark:bg-teal-900/50 dark:text-teal-300 border border-teal-300 dark:border-teal-700">
                Manajerial
            </span>
        @endif
    </div>

    <!-- Segmented Tabs Switcher -->
    <div class="grid grid-cols-2 p-1 bg-gray-100 dark:bg-gray-900/80 rounded-xl border border-gray-200/80 dark:border-gray-800">
        <!-- Tab Toolman -->
        <button
            type="button"
            wire:click="switchRole('toolman')"
            class="relative flex items-center justify-center gap-1.5 py-2 px-2.5 rounded-lg text-xs font-semibold transition-all duration-200 {{ $currentRole === 'toolman' ? 'bg-white dark:bg-gray-800 text-amber-600 dark:text-amber-400 shadow-sm border border-gray-200/60 dark:border-gray-700' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}"
        >
            <svg class="w-3.5 h-3.5 {{ $currentRole === 'toolman' ? 'text-amber-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.07a4.5 4.5 0 004.486-6.32l-3.276 3.277a3.004 3.004 0 01-2.25 2.25l-3.276-3.276a4.5 4.5 0 00-6.32 4.486c.118.58.094 1.193-.07 1.742m10.963 1.163l-2.496 3.03" />
            </svg>
            <span>Toolman</span>
        </button>

        <!-- Tab Kepala Lab -->
        <button
            type="button"
            wire:click="switchRole('kepala_lab')"
            class="relative flex items-center justify-center gap-1.5 py-2 px-2.5 rounded-lg text-xs font-semibold transition-all duration-200 {{ $currentRole === 'kepala_lab' ? 'bg-white dark:bg-gray-800 text-teal-600 dark:text-teal-400 shadow-sm border border-gray-200/60 dark:border-gray-700' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}"
        >
            <svg class="w-3.5 h-3.5 {{ $currentRole === 'kepala_lab' ? 'text-teal-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 01-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.636 50.636 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-5.25 6.557c0 1.875 1.5 3.75 5.25 3.75s5.25-1.875 5.25-3.75" />
            </svg>
            <span>Kepala Lab</span>
        </button>
    </div>

    <!-- Keterangan Tugas Singkat -->
    <div class="mt-2 text-[11px] text-gray-500 dark:text-gray-400 px-1 leading-snug">
        @if($currentRole === 'toolman')
            <span class="text-amber-700 dark:text-amber-300 font-medium">Fungsi Toolman:</span> Input barang masuk, keluar & peminjaman praktikum.
        @else
            <span class="text-teal-700 dark:text-teal-300 font-medium">Fungsi Kalab:</span> Buat daftar barang master & usulan pengadaan lab.
        @endif
    </div>
</div>
