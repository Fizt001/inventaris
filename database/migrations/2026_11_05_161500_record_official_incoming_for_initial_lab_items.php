<?php

use App\Models\Item;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $userId = User::where('email', 'kepala.tek@inventaris.smk')->value('id') ?? User::value('id') ?? 1;

        $itemsToRegister = [
            'UKUR-001' => 10,
            'TANG-001' => 15,
            'BHN-001'  => 20,
        ];

        foreach ($itemsToRegister as $kode => $qty) {
            $item = Item::where('kode', $kode)->first();
            if (! $item) {
                continue;
            }

            // Cek apakah sudah pernah ada transaksi masuk
            $exists = Transaction::where('item_id', $item->id)->where('tipe', 'masuk')->exists();
            if (! $exists) {
                Transaction::create([
                    'kode_transaksi' => Transaction::generateKode('masuk'),
                    'item_id'        => $item->id,
                    'unit_asal_id'   => null,
                    'unit_tujuan_id' => $item->unit_id,
                    'tipe'           => 'masuk',
                    'sumber'         => 'Yayasan',
                    'qty'            => $qty,
                    'harga_saat_itu' => $item->harga_satuan ?? 0,
                    'nilai_total'    => $qty * ($item->harga_satuan ?? 0),
                    'ref_type'       => 'barang_masuk',
                    'catatan'        => "Penerimaan Tgl 2026-09-20: {$qty} (Yayasan)",
                    'user_id'        => $userId,
                    'created_at'     => Carbon::parse('2026-09-20 12:00:00'),
                ]);
            }
        }
    }

    public function down(): void
    {
        $items = Item::whereIn('kode', ['UKUR-001', 'TANG-001', 'BHN-001'])->pluck('id');
        Transaction::whereIn('item_id', $items)
            ->where('tipe', 'masuk')
            ->whereDate('created_at', '2026-11-01')
            ->delete();
    }
};

