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
        Schema::create('prediction_results', function (Blueprint $table) {
            $table->id();
            
            // Input data untuk prediksi
            $table->string('nama')->nullable();
            $table->string('durasi_penggunaan'); // <=1 jam, 1-3 jam, >3 jam
            $table->string('frekuensi_akses'); // 1-2x, 3-5x, >5x
            $table->integer('perhatian_konten'); // 1-5 scale
            $table->integer('penghayatan'); // 1-5 scale
            
            // Hasil prediksi
            $table->string('predicted_label'); // RENDAH, SEDANG, TINGGI
            $table->json('prediction_details')->nullable(); // Decision path, confidence scores
            $table->decimal('confidence_score', 5, 4)->nullable(); // 0.0000 - 1.0000
            
            // Model info
            $table->unsignedBigInteger('training_session_id')->nullable();
            $table->string('model_type')->default('C4.5'); // C4.5, etc.
            $table->decimal('model_accuracy', 5, 2)->nullable(); // Model accuracy percentage
            
            // Additional info
            $table->text('notes')->nullable(); // User notes
            $table->string('user_agent')->nullable(); // Browser info
            $table->ipAddress('ip_address')->nullable(); // User IP
            
            $table->timestamps();
            
            // Indexes
            $table->index(['predicted_label', 'created_at']);
            $table->index('training_session_id');
            $table->index('created_at');
            
            // Foreign key
            $table->foreign('training_session_id')->references('id')->on('training_sessions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prediction_results');
    }
};
