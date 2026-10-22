<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->enum('role', ['student', 'admin'])->default('student');
            $t->string('phone', 30)->nullable();
            $t->boolean('is_active')->default(true);
        });
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->text('description')->nullable();
            $t->timestamps();
        });
        Schema::create('subjects', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->timestamps();
        });
        Schema::create('materials', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('category_id')->constrained()->restrictOnDelete();
            $t->foreignId('subject_id')->constrained()->restrictOnDelete();
            $t->string('title', 150);
            $t->text('description');
            $t->unsignedTinyInteger('semester');
            $t->decimal('price', 10, 2);
            $t->string('image_path')->nullable();
            $t->enum('condition', ['new', 'like_new', 'good', 'fair']);
            $t->enum('status', ['available', 'sold'])->default('available');
            $t->timestamps();
            $t->index(['status', 'created_at']);
            $t->index(['semester', 'price']);
        });
        Schema::create('enquiries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('material_id')->constrained()->cascadeOnDelete();
            $t->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $t->text('message');
            $t->text('response')->nullable();
            $t->enum('status', ['pending', 'responded', 'closed'])->default('pending');
            $t->timestamps();
        });
        Schema::create('reports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('material_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $t->text('reason');
            $t->enum('status', ['pending', 'reviewed', 'resolved'])->default('pending');
            $t->timestamps();
            $t->unique(['material_id', 'reporter_id']);
        });
    }

    public function down(): void
    {
        foreach (['reports', 'enquiries', 'materials', 'subjects', 'categories'] as $name) {
            Schema::dropIfExists($name);
        }Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['role', 'phone', 'is_active']));
    }
};
