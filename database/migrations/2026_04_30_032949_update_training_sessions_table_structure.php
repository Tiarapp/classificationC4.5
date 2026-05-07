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
        Schema::table('training_sessions', function (Blueprint $table) {
            // Rename columns to match our model
            $table->renameColumn('model_name', 'algorithm');
            $table->renameColumn('training_samples_count', 'train_data_count');
            $table->renameColumn('testing_samples_count', 'test_data_count');
            $table->renameColumn('decision_tree', 'model_data');
            
            // Add missing columns
            $table->json('parameters')->nullable()->after('algorithm');
            $table->decimal('training_time', 8, 4)->after('accuracy');
            
            // Drop unnecessary columns
            $table->dropColumn([
                'precision', 
                'recall', 
                'f1_score', 
                'confusion_matrix',
                'model_path', 
                'status', 
                'training_started_at', 
                'training_completed_at'
            ]);
            
            // Update model_data column type to longText
            $table->longText('model_data')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            // Reverse the changes
            $table->renameColumn('algorithm', 'model_name');
            $table->renameColumn('train_data_count', 'training_samples_count');
            $table->renameColumn('test_data_count', 'testing_samples_count');
            $table->renameColumn('model_data', 'decision_tree');
            
            // Remove added columns
            $table->dropColumn(['parameters', 'training_time']);
            
            // Add back removed columns
            $table->decimal('precision', 5, 4)->nullable();
            $table->decimal('recall', 5, 4)->nullable();
            $table->decimal('f1_score', 5, 4)->nullable();
            $table->json('confusion_matrix')->nullable();
            $table->text('model_path')->nullable();
            $table->enum('status', ['training', 'completed', 'failed'])->default('training');
            $table->timestamp('training_started_at')->nullable();
            $table->timestamp('training_completed_at')->nullable();
        });
    }
};
