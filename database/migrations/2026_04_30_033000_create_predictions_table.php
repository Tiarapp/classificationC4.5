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
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_session_id')->constrained()->onDelete('cascade');
            $table->enum('durasi_penggunaan', ['<=1 jam', '1-3 jam', '3-5 jam', '>5 jam']);
            $table->enum('frekuensi_akses', ['1-2x', '3-5x', '>5x']);
            $table->integer('perhatian_konten'); // 1-5 scale
            $table->integer('penghayatan'); // 1-5 scale
            $table->enum('predicted_label', ['rendah', 'sedang', 'tinggi']);
            $table->decimal('confidence_score', 5, 4)->nullable();
            $table->json('decision_path')->nullable(); // Path through the decision tree
            $table->string('predicted_by')->nullable(); // IP address or user identifier
            $table->timestamps();

            $table->index(['predicted_label', 'confidence_score']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predictions');
    }
};
