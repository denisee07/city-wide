<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('census_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barangay_id')->nullable()->constrained('barangays')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('household_no')->nullable();
            $table->string('full_name');
            $table->enum('sex', ['male', 'female'])->nullable();
            $table->date('birth_date')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->unsignedTinyInteger('household_size')->default(1);
            $table->string('purok')->nullable();
            $table->text('address')->nullable();
            $table->string('occupation')->nullable();
            $table->string('income_bracket')->nullable();
            $table->boolean('is_head')->default(false);
            $table->string('civil_status')->nullable();
            $table->string('education')->nullable();
            $table->string('religion')->nullable();
            $table->boolean('is_pwd')->default(false);
            $table->boolean('is_senior')->default(false);
            $table->timestamps();
            $table->unique(['barangay_id', 'household_no', 'full_name'], 'census_unique_member');
            $table->index(['barangay_id', 'sex']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('census_records');
    }
};