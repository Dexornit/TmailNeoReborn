<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('emails')) {
            return;
        }

        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('domain');
            $table->string('email')->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('status', ['active', 'disabled', 'revoked'])->default('active');
            $table->boolean('is_protected')->default(false);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'deleted_at']);
            $table->index('domain');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};
