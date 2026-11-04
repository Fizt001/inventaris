<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pengajuan Anggaran (Header)
        Schema::create('budget_proposals', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->foreignId('unit_id')->constrained('units');
            $table->string('judul');
            $table->enum('jenis', ['semesteran', 'tahunan'])->default('semesteran');
            $table->string('periode_label');  // "Semester 1 TA 2024/2025" / "Tahun 2025"
            $table->string('tahun_ajaran', 20)->nullable();
            $table->decimal('total_estimasi', 15, 2)->default(0);
            $table->enum('status', [
                'draft', 'diajukan', 'disetujui', 'ditolak', 'direalisasi'
            ])->default('draft');
            $table->foreignId('diajukan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('tgl_diajukan')->nullable();
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('tgl_disetujui')->nullable();
            $table->text('catatan_pengaju')->nullable();
            $table->text('catatan_wakasarpras')->nullable();
            $table->timestamps();
        });

        // Detail Item Pengajuan Anggaran
        Schema::create('budget_proposal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_proposal_id')->constrained('budget_proposals')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->string('nama_item_override')->nullable(); // untuk item baru
            $table->string('kategori_manual')->nullable();
            $table->integer('qty_dibutuhkan');
            $table->string('satuan', 30)->default('pcs');
            $table->decimal('harga_referensi', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->enum('prioritas', ['wajib', 'penting', 'tambahan'])->default('wajib');
            $table->text('keterangan')->nullable();
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        // Audit Trail Status Pengajuan
        Schema::create('budget_proposal_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_proposal_id')->constrained('budget_proposals')->cascadeOnDelete();
            $table->string('status_dari')->nullable();
            $table->string('status_ke');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // Purchase Orders (Pengadaan)
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('kode_po')->unique();
            $table->foreignId('unit_id')->constrained('units');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('budget_proposal_id')->nullable()->constrained('budget_proposals')->nullOnDelete();
            $table->date('tgl_pengajuan');
            $table->date('tgl_dibutuhkan')->nullable();
            $table->enum('status', [
                'draft', 'diajukan', 'diproses', 'diterima_sebagian', 'selesai', 'dibatalkan'
            ])->default('draft');
            $table->decimal('total_estimasi', 15, 2)->default(0);
            $table->decimal('total_realisasi', 15, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Detail Item PO
        Schema::create('po_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items');
            $table->integer('qty_dipesan');
            $table->integer('qty_diterima')->default(0);
            $table->decimal('harga_satuan_estimasi', 15, 2)->default(0);
            $table->decimal('harga_satuan_realisasi', 15, 2)->default(0);
            $table->decimal('subtotal_realisasi', 15, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // Penerimaan Barang (per PO bisa multi-batch)
        Schema::create('po_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->string('kode_penerimaan')->unique();
            $table->date('tgl_terima');
            $table->text('catatan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Detail Item per Penerimaan
        Schema::create('po_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_receipt_id')->constrained('po_receipts')->cascadeOnDelete();
            $table->foreignId('po_item_id')->constrained('po_items');
            $table->foreignId('item_id')->constrained('items');
            $table->integer('qty_diterima');
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->enum('kondisi', ['baik', 'cacat'])->default('baik');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // Permintaan Barang ke Gudang
        Schema::create('warehouse_requests', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->foreignId('unit_peminta_id')->constrained('units');
            $table->foreignId('unit_gudang_id')->constrained('units');
            $table->enum('status', ['draft', 'diajukan', 'disetujui', 'dikirim', 'selesai', 'ditolak'])->default('draft');
            $table->text('catatan')->nullable();
            $table->date('tgl_dibutuhkan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Detail Item Permintaan Gudang
        Schema::create('warehouse_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_request_id')->constrained('warehouse_requests')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items');
            $table->integer('qty_diminta');
            $table->integer('qty_dikirim')->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_request_items');
        Schema::dropIfExists('warehouse_requests');
        Schema::dropIfExists('po_receipt_items');
        Schema::dropIfExists('po_receipts');
        Schema::dropIfExists('po_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('budget_proposal_logs');
        Schema::dropIfExists('budget_proposal_items');
        Schema::dropIfExists('budget_proposals');
    }
};
