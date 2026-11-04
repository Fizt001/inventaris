<x-filament-panels::page>
    {{-- ═══ STATS BANNER / QUICK OVERVIEW (1 BARIS x 4) ═══ --}}
    <style>
        .lab-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }
        @media (max-width: 900px) {
            .lab-stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 480px) {
            .lab-stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <div class="lab-stats-grid">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-xs dark:border-white/10 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                    <x-filament::icon icon="heroicon-o-archive-box" class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Katalog Barang Terdaftar</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $this->getTotalItemCount() }} <span class="text-xs font-normal text-gray-400">item</span></p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-xs dark:border-white/10 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                    <x-filament::icon icon="heroicon-o-check-circle" class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Stok Fisik Tersedia</p>
                    <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ $this->getTotalStokTersedia() }} <span class="text-xs font-normal text-gray-400">unit</span></p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-xs dark:border-white/10 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                    <x-filament::icon icon="heroicon-o-clock" class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Sedang Dipinjam Siswa/Guru</p>
                    <p class="text-xl font-bold text-amber-600 dark:text-amber-400">{{ $this->getTotalStokDipinjam() }} <span class="text-xs font-normal text-gray-400">unit</span></p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-xs dark:border-white/10 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400">
                    <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Perlu Pengadaan (Menipis)</p>
                    <p class="text-xl font-bold text-rose-600 dark:text-rose-400">{{ $this->getStokMenipisCount() }} <span class="text-xs font-normal text-gray-400">item</span></p>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ TABEL WADAH OTOMATIS INVENTARIS LAB ═══ --}}
    <div class="mt-4">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
