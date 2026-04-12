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
        Schema::create('pengajuans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nomor_hp')->nullable();
            $table->enum('tipe_pengajuan', ['ruangan', 'alat', 'pengujian']);
            $table->enum('status', ['diajukan', 'diverifikasi', 'ditolak', 'disetujui', 'selesai'])->default('diajukan');
            $table->string('judul_proyek')->nullable();
            $table->text('tujuan_penggunaan')->nullable();
            $table->string('dosen_pembimbing')->nullable();
            $table->string('email_dosen')->nullable();
            $table->string('surat_pengantar_path')->nullable();
            $table->text('catatan_reviewer')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuans');
    }
};
