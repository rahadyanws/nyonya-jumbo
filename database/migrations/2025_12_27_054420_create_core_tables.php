<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Karyawan (HR)
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('position'); // Dapur, Kasir, Kurir
            $table->decimal('base_salary', 12, 2); // Gaji Pokok
            $table->decimal('daily_allowance', 12, 2)->default(0); // Uang Harian
            $table->date('join_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Absensi (HR)
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('clock_in');
            $table->time('clock_out')->nullable();
            $table->enum('status', ['present', 'late', 'absent', 'sick'])->default('present');
            $table->timestamps();
        });

        // 3. Bahan Baku (Inventory)
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tepung, Ayam, Udang
            $table->string('unit'); // kg, gram, pcs, liter
            $table->integer('alert_limit')->default(5); // Notifikasi jika stok < 5
            $table->timestamps();
        });

        // 4. Belanja Harian (Procurement Header)
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date');
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->string('supplier_name')->nullable(); // Pasar A, Toko B
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. Item Belanja (Procurement Detail - Untuk Tracking Harga)
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained();
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 12, 2); // Harga per unit saat beli (PENTING untuk grafik)
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });

        // 6. Produk (Katalog Dinsum untuk Pelanggan)
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category'); // Kukus, Goreng, Frozen
            $table->decimal('price', 10, 2);
            $table->string('image_path')->nullable();
            $table->boolean('is_available')->default(true); // Toggle Sold Out
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('ingredients');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('employees');
    }
};
