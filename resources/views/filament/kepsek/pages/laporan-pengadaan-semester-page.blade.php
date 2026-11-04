<x-filament-panels::page>
    @php
        $stats = $this->getExecutiveStats();
    @endphp

    <div class="space-y-6">
        <!-- Header Penjelasan Kepala Sekolah -->
        <div class="p-4 bg-violet-50 dark:bg-violet-950/40 border border-violet-200 dark:border-violet-800 rounded-xl flex items-start gap-3">
            <div class="p-2 bg-violet-100 dark:bg-violet-900 rounded-lg text-violet-700 dark:text-violet-300 mt-0.5">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.25 2.25L15 6" />
                </svg>
            </div>
            <div class="flex-1 text-sm text-violet-900 dark:text-violet-200">
                <p class="font-semibold text-base mb-0.5">Laporan Eksekutif Pengadaan Barang per Semester</p>
                <p class="text-xs text-violet-700 dark:text-violet-300">
                    Halaman ini menampilkan rangkuman resmi usulan pengadaan alat dan bahan praktik yang diajukan oleh masing-masing <strong>Kepala Laboratorium</strong> dan telah diverifikasi oleh <strong>Wakil Kepala Sarana & Prasarana</strong> untuk pertimbangan penganggaran semester berjalan.
                </p>
            </div>
        </div>

        <!-- Metric Cards Eksekutif -->
        <style>
            .kepsek-stats-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 16px;
            }
            @media (max-width: 768px) {
                .kepsek-stats-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
        <div class="kepsek-stats-grid">
            <!-- Total Usulan -->
            <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-4">
                <div class="p-3 bg-violet-100 dark:bg-violet-950/60 text-violet-600 dark:text-violet-400 rounded-xl">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Total Usulan Diajukan</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_usulan'] }} Dokumen</div>
                    <div class="text-[11px] text-gray-500 dark:text-gray-400">dari seluruh jurusan & lab</div>
                </div>
            </div>

            <!-- Total Anggaran Diajukan -->
            <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-4">
                <div class="p-3 bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 rounded-xl">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                    </svg>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Total Anggaran Diusulkan</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($stats['total_anggaran'], 0, ',', '.') }}</div>
                    <div class="text-[11px] text-blue-600 dark:text-blue-400">akumulasi seluruh usulan lab</div>
                </div>
            </div>

            <!-- Anggaran Disetujui Waka -->
            <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-4">
                <div class="p-3 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-xl">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Usulan Disetujui Waka</div>
                    <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($stats['disetujui_anggaran'], 0, ',', '.') }}</div>
                    <div class="text-[11px] text-emerald-600 dark:text-emerald-400">{{ $stats['disetujui_count'] }} usulan siap dieksekusi</div>
                </div>
            </div>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>

