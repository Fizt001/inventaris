<x-filament-panels::page>
    @php
        $stats = $this->getSummaryStats();
        $units = \App\Models\Unit::orderBy('nama')->get();
    @endphp

    <div class="space-y-6">
        <!-- Filter Bar -->
        <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex flex-wrap gap-4 items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Periode:</span>
                <select wire:model.live="selectedMonth" class="text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 py-1.5 px-3">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}</option>
                    @endforeach
                </select>

                <select wire:model.live="selectedYear" class="text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 py-1.5 px-3">
                    @foreach(range(date('Y') - 1, date('Y') + 1) as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Jurusan / Lab:</span>
                <select wire:model.live="selectedUnitId" class="text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 py-1.5 px-3">
                    <option value="">Semua Jurusan & Gudang</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}">{{ $u->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Metric Cards -->
        <style>
            .waka-stats-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 16px;
            }
            @media (max-width: 768px) {
                .waka-stats-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
        <div class="waka-stats-grid">
            <!-- Total Masuk -->
            <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-4">
                <div class="p-3 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-xl">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3" />
                    </svg>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Total Barang Masuk</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_masuk'], 0, ',', '.') }}</div>
                    <div class="text-[11px] text-emerald-600 dark:text-emerald-400">item bertambah ke lab</div>
                </div>
            </div>

            <!-- Total Keluar -->
            <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-4">
                <div class="p-3 bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 rounded-xl">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18" />
                    </svg>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Total Barang Keluar / Terpakai</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_keluar'], 0, ',', '.') }}</div>
                    <div class="text-[11px] text-rose-600 dark:text-rose-400">bahan terpakai praktikum</div>
                </div>
            </div>

            <!-- Total Peminjaman -->
            <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-4">
                <div class="p-3 bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 rounded-xl">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                    </svg>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Sesi Peminjaman Praktik</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_peminjaman'], 0, ',', '.') }}</div>
                    <div class="text-[11px] text-amber-600 dark:text-amber-400">aktivitas siswa/guru di lab</div>
                </div>
            </div>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>

