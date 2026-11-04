<div class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-amber-900 dark:border-amber-700/60 dark:bg-amber-950/40 dark:text-amber-200">
    <div class="flex items-start gap-3">
        <x-filament::icon
            icon="heroicon-o-exclamation-triangle"
            class="h-6 w-6 shrink-0 text-amber-600 dark:text-amber-400"
        />
        <div class="flex-1 text-sm">
            <h4 class="font-semibold">Master Kategori Belum Tersedia</h4>
            <p class="mt-1 text-amber-800 dark:text-amber-300">
                Sesuai aturan sistem, Anda harus membuat minimal <strong>satu Master Kategori / Kelompok</strong> terlebih dahulu sebelum dapat mencatat inventaris alat dan bahan lab.
            </p>
            <div class="mt-3">
                <x-filament::button
                    tag="a"
                    href="/kepalab/categories"
                    size="sm"
                    color="warning"
                    icon="heroicon-m-plus-circle"
                >
                    Buat Master Kategori Sekarang
                </x-filament::button>
            </div>
        </div>
    </div>
</div>
