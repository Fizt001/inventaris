<div class="space-y-4">
    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700">
        <div>
            <span class="text-xs text-gray-500 dark:text-gray-400">Total Inventaris Terdaftar</span>
            <p class="text-lg font-bold text-gray-900 dark:text-white">
                {{ $area->items->count() }} <span class="text-sm font-normal text-gray-500">item / barang</span>
            </p>
        </div>
        <a href="{{ url('/admin/items/create') }}"
           target="_blank"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-500 transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Barang Baru
        </a>
    </div>

    @if($area->items->count() > 0)
        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                <thead class="bg-gray-100 dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Nama Barang</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Tipe</th>
                        <th class="px-4 py-3">Posisi Rak</th>
                        <th class="px-4 py-3 text-center">Stok</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($area->items as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                            <td class="px-4 py-3 font-mono font-medium text-xs text-indigo-600 dark:text-indigo-400">
                                {{ $item->kode }}
                            </td>
                            <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">
                                {{ $item->nama }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-block px-2 py-0.5 text-xs rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                    {{ $item->category?->nama ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs">
                                <span class="font-medium {{ $item->tipe_barang === 'consumable' ? 'text-rose-600' : ($item->tipe_barang === 'returnable' ? 'text-amber-600' : 'text-emerald-600') }}">
                                    {{ \App\Models\Item::TIPE_LABELS[$item->tipe_barang] ?? $item->tipe_barang }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">
                                {{ $item->rak_posisi ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-xs">
                                {{ $item->stock?->stok_tersedia ?? 0 }} {{ $item->satuan }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="p-8 text-center rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
            <div class="mx-auto w-12 h-12 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400 mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
            </div>
            <h4 class="font-semibold text-gray-800 dark:text-gray-200">Belum ada inventaris di area ini</h4>
            <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                Kepala Lab dapat mendaftarkan alat, bahan, atau aset tetap ke area ini melalui form Master Barang.
            </p>
        </div>
    @endif
</div>
