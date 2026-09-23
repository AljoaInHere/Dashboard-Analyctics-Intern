<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sms_traffics', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('operator'); // Contoh: Telkomsel, Indosat, XL
            $table->integer('total_dikirim');
            $table->integer('total_sukses');
            $table->integer('total_gagal');
            $table->timestamps();

            // Indexes for fast aggregation and filtering
            $table->index('tanggal');
            $table->index(['tanggal', 'operator']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_traffics');
    }
};
