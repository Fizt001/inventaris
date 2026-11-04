<x-filament-panels::page>
    <div class="space-y-6">
        <div class="p-4 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-xl flex items-start gap-3">
            <div class="p-2 bg-amber-100 dark:bg-amber-900 rounded-lg text-amber-700 dark:text-amber-300 mt-0.5">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                </svg>
            </div>
            <div class="flex-1 text-sm text-amber-900 dark:text-amber-200">
                <p class="font-semibold text-base mb-0.5">Verifikasi Pengajuan Pengadaan Semesteran</p>
                <p class="text-xs text-amber-700 dark:text-amber-300">
                    Sebagai <strong>Wakil Kepala Sarana & Prasarana</strong>, Anda dapat meninjau kebutuhan alat dan bahan yang diajukan oleh masing-masing Kepala Lab/Jurusan. Usulan yang disetujui akan dirangkum ke dalam Laporan Eksekutif untuk Kepala Sekolah.
                </p>
            </div>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>

