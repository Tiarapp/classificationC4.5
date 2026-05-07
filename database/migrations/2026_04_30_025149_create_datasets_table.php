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
        Schema::create('datasets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('respondent_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('durasi_penggunaan', ['<=1 jam', '1-3 jam', '3-5 jam', '>5 jam']);
            $table->enum('frekuensi_akses', ['1-2x', '3-5x', '>5x']);
            $table->integer('perhatian_konten'); // 1-5 scale
            $table->integer('penghayatan'); // 1-5 scale
            $table->enum('label_intensitas', ['rendah', 'sedang', 'tinggi']);
            $table->boolean('is_training_data')->default(true);
            $table->timestamps();

            $table->index(['durasi_penggunaan', 'frekuensi_akses']);
            $table->index(['label_intensitas', 'is_training_data']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('datasets');
    }
};
