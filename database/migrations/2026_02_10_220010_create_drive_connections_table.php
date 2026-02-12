<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drive_connections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('google_account_id');
            $table->string('google_account_email');

            $table->longText('refresh_token_enc');
            $table->longText('access_token_enc')->nullable();
            $table->timestamp('access_token_expires_at')->nullable();

            $table->json('scopes');
            $table->string('status', 32);
            $table->string('created_via', 32);
            $table->boolean('is_default')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'google_account_id']);
            $table->unique(['user_id', 'name']);
            $table->index(['user_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drive_connections');
    }
};

