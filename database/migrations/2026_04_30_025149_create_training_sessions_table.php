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
        Schema::create('training_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('model_name')->default('C4.5_DecisionTree');
            $table->integer('training_samples_count');
            $table->integer('testing_samples_count');
            $table->decimal('accuracy', 5, 4)->nullable();
            $table->decimal('precision', 5, 4)->nullable();
            $table->decimal('recall', 5, 4)->nullable();
            $table->decimal('f1_score', 5, 4)->nullable();
            $table->json('confusion_matrix')->nullable();
            $table->json('decision_tree')->nullable();
            $table->text('model_path')->nullable();
            $table->enum('status', ['training', 'completed', 'failed'])->default('training');
            $table->timestamp('training_started_at')->nullable();
            $table->timestamp('training_completed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('accuracy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_sessions');
    }
};
