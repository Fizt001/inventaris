<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Saldo Stok Real-time per Item per Unit
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->integer('stok_tersedia')->default(0);
            $table->integer('stok_dipinjam')->default(0);
            $table->unique(['item_id', 'unit_id']);
            $table->timestamps();
        });

        // Log Semua Pergerakan Barang (append-only)
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi')->unique();
            $table->foreignId('item_id')->constrained('items');
            $table->foreignId('unit_asal_id')->nullable()->constrained('units')->nullOnDelete();
            $table->foreignId('unit_tujuan_id')->nullable()->constrained('units')->nullOnDelete();
            $table->enum('tipe', [
                'masuk', 'keluar', 'pinjam', 'kembali',
                'transfer', 'adjustment', 'disposal'
            ]);
            $table->integer('qty');
            $table->decimal('harga_saat_itu', 15, 2)->default(0);
            $table->decimal('nilai_total', 15, 2)->default(0);
            $table->string('ref_type')->nullable();  // polymorphic
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('stocks');
    }
};
