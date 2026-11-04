<div class="space-y-4 text-sm">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 p-3 bg-gray-50 dark:bg-gray-800/60 rounded-xl border border-gray-200 dark:border-gray-700">
        <div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Kode Usulan</div>
            <div class="font-mono font-bold text-gray-900 dark:text-white">{{ $proposal->kode }}</div>
        </div>
        <div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Periode</div>
            <div class="font-semibold text-gray-900 dark:text-white">{{ $proposal->periode_label }}</div>
        </div>
        <div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Tahun Ajaran</div>
            <div class="font-semibold text-gray-900 dark:text-white">{{ $proposal->tahun_ajaran ?? '-' }}</div>
        </div>
        <div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Total Estimasi</div>
            <div class="font-bold text-teal-600 dark:text-teal-400">Rp {{ number_format($proposal->total_estimasi, 0, ',', '.') }}</div>
        </div>
    </div>

    @if($proposal->catatan_pengaju)
        <div class="p-3 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-lg text-xs text-amber-900 dark:text-amber-200">
            <strong>Catatan Pengaju:</strong> {{ $proposal->catatan_pengaju }}
        </div>
    @endif

    @if($proposal->catatan_wakasarpras)
        <div class="p-3 bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-lg text-xs text-blue-900 dark:text-blue-200">
            <strong>Catatan Wakasarpras:</strong> {{ $proposal->catatan_wakasarpras }}
        </div>
    @endif

    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
        <table class="w-full text-left text-xs divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-100 dark:bg-gray-800 font-semibold text-gray-700 dark:text-gray-300">
                <tr>
                    <th class="px-3 py-2.5 w-10 text-center">No</th>
                    <th class="px-3 py-2.5">Nama Alat / Bahan Praktik</th>
                    <th class="px-3 py-2.5 text-center">Qty</th>
                    <th class="px-3 py-2.5">Satuan</th>
                    <th class="px-3 py-2.5 text-right">Est. Harga</th>
                    <th class="px-3 py-2.5 text-right">Subtotal</th>
                    <th class="px-3 py-2.5 text-center">Prioritas</th>
                    <th class="px-3 py-2.5">Kegunaan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                @forelse($proposal->items as $idx => $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-3 py-2 text-center text-gray-500">{{ $idx + 1 }}</td>
                        <td class="px-3 py-2 font-medium text-gray-900 dark:text-white">{{ $item->nama }}</td>
                        <td class="px-3 py-2 text-center font-bold text-gray-800 dark:text-gray-200">{{ $item->qty_dibutuhkan }}</td>
                        <td class="px-3 py-2 text-gray-600 dark:text-gray-400">{{ $item->satuan }}</td>
                        <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-400">Rp {{ number_format($item->harga_referensi, 0, ',', '.') }}</td>
                        <td class="px-3 py-2 text-right font-bold text-teal-600 dark:text-teal-400">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        <td class="px-3 py-2 text-center">
                            @if($item->prioritas === 'wajib')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">Wajib</span>
                            @elseif($item->prioritas === 'penting')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">Penting</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">Tambahan</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-gray-500 dark:text-gray-400 italic">{{ $item->keterangan ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-3 py-4 text-center text-gray-400">Belum ada item barang yang diinput.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="bg-gray-50 dark:bg-gray-800 font-bold text-gray-900 dark:text-white">
                <tr>
                    <td colspan="5" class="px-3 py-2.5 text-right uppercase tracking-wider text-[11px]">Total Estimasi Kebutuhan Anggaran:</td>
                    <td class="px-3 py-2.5 text-right text-teal-600 dark:text-teal-400 text-sm">
                        Rp {{ number_format($proposal->total_estimasi, 0, ',', '.') }}
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

