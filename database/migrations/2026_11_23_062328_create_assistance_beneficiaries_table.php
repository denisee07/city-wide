<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assistance_beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assistance_program_id')->constrained('assistance_programs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('reference_no')->unique();
            $table->enum('status', ['qualified', 'claimed', 'unclaimed'])->default('qualified');
            $table->string('qr_code')->unique()->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->foreignId('claimed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['assistance_program_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assistance_beneficiaries');
    }
};