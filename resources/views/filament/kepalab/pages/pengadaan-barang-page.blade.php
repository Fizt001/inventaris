<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Banner Penjelasan Fungsi Kepala Lab -->
        <div class="p-4 bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800 rounded-xl flex items-start gap-3">
            <div class="p-2 bg-teal-100 dark:bg-teal-900 rounded-lg text-teal-700 dark:text-teal-300 mt-0.5">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                </svg>
            </div>
            <div class="flex-1 text-sm text-teal-900 dark:text-teal-200">
                <p class="font-semibold text-base mb-0.5">Fitur Perencanaan & Usulan Pengadaan Semesteran</p>
                <p class="text-xs text-teal-700 dark:text-teal-300">
                    Sebagai <strong>Kepala Lab</strong>, Anda bertanggung jawab merencanakan kebutuhan alat, komponen, dan bahan praktik per semester atau tahun ajaran. Data usulan ini akan diteruskan ke <strong>Wakasarpras</strong> untuk direkap dan dimonitor oleh <strong>Kepala Sekolah</strong>.
                </p>
            </div>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>

