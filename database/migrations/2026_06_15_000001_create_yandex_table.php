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
        Schema::create('parse_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('url');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('user_id');
        });

        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('parse_task_id')->constrained()->onDelete('cascade');
            $table->string('yandex_business_id', 100)->nullable();
            $table->string('name', 500);
            $table->text('address')->nullable();
            $table->boolean('needs_update')->default(false);
            $table->decimal('avg_rating', 3, 2)->nullable();
            $table->integer('total_ratings_count')->default(0);
            $table->integer('total_reviews_count')->default(0);
            $table->timestamp('parsed_at')->useCurrent();
            $table->timestamps();

            $table->index('user_id');
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('yandex_review_id', 100)->nullable();
            $table->string('author_name', 255)->nullable();
            $table->unsignedTinyInteger('rating')->nullable()->comment('1-5 stars');
            $table->text('review_text')->nullable();
            $table->timestamp('review_date')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'yandex_review_id'], 'unique_org_review');
            $table->index('rating');
            $table->index('review_date');
        });

        Schema::create('parse_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parse_task_id')->constrained()->onDelete('cascade');
            $table->enum('log_level', ['INFO', 'WARNING', 'ERROR'])->default('INFO');
            $table->text('message');
            $table->timestamps();

            $table->index('parse_task_id');
            $table->index('log_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parse_tasks');
        Schema::dropIfExists('organizations');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('parse_logs');
    }
};
