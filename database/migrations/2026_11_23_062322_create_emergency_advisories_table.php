<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_advisories', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('category')->default('general'); // weather|flood|evacuation|earthquake|fire|safety|hotline|general
            $table->enum('scope', ['citywide', 'barangay'])->default('citywide');
            $table->foreignId('barangay_id')->nullable()->constrained('barangays')->nullOnDelete();
            $table->boolean('is_critical')->default(false);
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_advisories');
    }
};