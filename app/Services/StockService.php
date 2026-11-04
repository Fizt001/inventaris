<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Stock;
use App\Models\Transaction;
use App\Models\PriceHistory;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Barang Masuk (dari pengadaan atau penerimaan)
     */
    public function tambahStok(
        Item $item,
        int $qty,
        int $unitId,
        float $harga = 0,
        string $catatan = '',
        ?string $refType = null,
        ?int $refId = null,
        ?int $userId = null
    ): Transaction {
        return DB::transaction(function () use ($item, $qty, $unitId, $harga, $catatan, $refType, $refId, $userId) {
            // Update atau buat saldo stok
            $stock = Stock::firstOrCreate(
                ['item_id' => $item->id, 'unit_id' => $unitId],
                ['stok_tersedia' => 0, 'stok_dipinjam' => 0]
            );
            $stock->increment('stok_tersedia', $qty);

            // Update harga di master barang jika ada harga aktual
            if ($harga > 0) {
                $item->update([
                    'harga_satuan'    => $harga,
                    'harga_referensi' => $harga,
                ]);
                // Simpan ke riwayat harga
                PriceHistory::create([
                    'item_id'  => $item->id,
                    'harga'    => $harga,
                    'tanggal'  => today(),
                    'sumber'   => 'pembelian',
                    'catatan'  => $catatan,
                    'user_id'  => $userId,
                ]);
            }

            // Log transaksi
            return Transaction::create([
                'kode_transaksi'  => Transaction::generateKode('masuk'),
                'item_id'         => $item->id,
                'unit_asal_id'    => null,
                'unit_tujuan_id'  => $unitId,
                'tipe'            => 'masuk',
                'qty'             => $qty,
                'harga_saat_itu'  => $harga,
                'nilai_total'     => $qty * $harga,
                'ref_type'        => $refType,
                'ref_id'          => $refId,
                'catatan'         => $catatan,
                'user_id'         => $userId,
            ]);
        });
    }

    /**
     * Barang Keluar (pemakaian habis pakai)
     */
    public function kurangiStok(
        Item $item,
        int $qty,
        int $unitId,
        string $catatan = '',
        ?int $userId = null
    ): Transaction {
        return DB::transaction(function () use ($item, $qty, $unitId, $catatan, $userId) {
            $stock = Stock::where('item_id', $item->id)->where('unit_id', $unitId)->firstOrFail();

            if ($stock->stok_tersedia < $qty) {
                throw new \Exception("Stok tidak mencukupi. Tersedia: {$stock->stok_tersedia}, Diminta: {$qty}");
            }

            $stock->decrement('stok_tersedia', $qty);

            return Transaction::create([
                'kode_transaksi' => Transaction::generateKode('keluar'),
                'item_id'        => $item->id,
                'unit_asal_id'   => $unitId,
                'tipe'           => 'keluar',
                'qty'            => $qty,
                'harga_saat_itu' => $item->harga_satuan,
                'nilai_total'    => $qty * $item->harga_satuan,
                'catatan'        => $catatan,
                'user_id'        => $userId,
            ]);
        });
    }

    /**
     * Pinjam Alat (returnable)
     */
    public function pinjamAlat(Item $item, int $qty, int $unitId, ?int $userId = null): void
    {
        DB::transaction(function () use ($item, $qty, $unitId, $userId) {
            $stock = Stock::where('item_id', $item->id)->where('unit_id', $unitId)->firstOrFail();

            if ($stock->stok_tersedia < $qty) {
                throw new \Exception("Stok tidak mencukupi untuk dipinjam.");
            }

            $stock->decrement('stok_tersedia', $qty);
            $stock->increment('stok_dipinjam', $qty);
        });
    }

    /**
     * Kembalikan Alat
     */
    public function kembalikanAlat(Item $item, int $qty, int $unitId, ?int $userId = null): void
    {
        DB::transaction(function () use ($item, $qty, $unitId, $userId) {
            $stock = Stock::where('item_id', $item->id)->where('unit_id', $unitId)->firstOrFail();
            $stock->decrement('stok_dipinjam', $qty);
            $stock->increment('stok_tersedia', $qty);
        });
    }

    /**
     * Adjustment / Opname Stok
     */
    public function adjustment(
        Item $item,
        int $stokFisik,
        int $unitId,
        string $catatan,
        ?int $userId = null
    ): Transaction {
        return DB::transaction(function () use ($item, $stokFisik, $unitId, $catatan, $userId) {
            $stock = Stock::firstOrCreate(
                ['item_id' => $item->id, 'unit_id' => $unitId],
                ['stok_tersedia' => 0, 'stok_dipinjam' => 0]
            );

            $selisih = $stokFisik - $stock->stok_tersedia;
            $stock->update(['stok_tersedia' => $stokFisik]);

            return Transaction::create([
                'kode_transaksi' => Transaction::generateKode('adjustment'),
                'item_id'        => $item->id,
                'unit_asal_id'   => $unitId,
                'unit_tujuan_id' => $unitId,
                'tipe'           => 'adjustment',
                'qty'            => abs($selisih),
                'harga_saat_itu' => $item->harga_satuan,
                'nilai_total'    => abs($selisih) * $item->harga_satuan,
                'catatan'        => "Opname stok. Selisih: {$selisih}. {$catatan}",
                'user_id'        => $userId,
            ]);
        });
    }

    /**
     * Transfer Stok antar Unit
     */
    public function transfer(
        Item $item,
        int $qty,
        int $unitAsalId,
        int $unitTujuanId,
        string $catatan = '',
        ?int $userId = null
    ): Transaction {
        return DB::transaction(function () use ($item, $qty, $unitAsalId, $unitTujuanId, $catatan, $userId) {
            $stockAsal = Stock::where('item_id', $item->id)->where('unit_id', $unitAsalId)->firstOrFail();

            if ($stockAsal->stok_tersedia < $qty) {
                throw new \Exception("Stok asal tidak mencukupi untuk transfer.");
            }

            $stockAsal->decrement('stok_tersedia', $qty);

            $stockTujuan = Stock::firstOrCreate(
                ['item_id' => $item->id, 'unit_id' => $unitTujuanId],
                ['stok_tersedia' => 0, 'stok_dipinjam' => 0]
            );
            $stockTujuan->increment('stok_tersedia', $qty);

            return Transaction::create([
                'kode_transaksi' => Transaction::generateKode('transfer'),
                'item_id'        => $item->id,
                'unit_asal_id'   => $unitAsalId,
                'unit_tujuan_id' => $unitTujuanId,
                'tipe'           => 'transfer',
                'qty'            => $qty,
                'harga_saat_itu' => $item->harga_satuan,
                'nilai_total'    => $qty * $item->harga_satuan,
                'catatan'        => $catatan,
                'user_id'        => $userId,
            ]);
        });
    }

    /**
     * Cek stok menipis — kembalikan daftar item yang di bawah minimum
     */
    public function getStokMenipis(?int $unitId = null): \Illuminate\Database\Eloquent\Collection
    {
        return Stock::with('item', 'unit')
            ->when($unitId, fn ($q) => $q->where('unit_id', $unitId))
            ->whereColumn('stok_tersedia', '<=', DB::raw('(SELECT stok_minimum FROM items WHERE items.id = stocks.item_id)'))
            ->get();
    }
}
