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
        Schema::table('users', function (Blueprint $table) {
            $table->string('telegram_chat_id')->nullable()->unique()->after('theme');
            $table->string('telegram_connect_token', 32)->nullable()->index()->after('telegram_chat_id');
            $table->timestamp('telegram_connect_token_expires_at')->nullable()->after('telegram_connect_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'telegram_chat_id',
                'telegram_connect_token',
                'telegram_connect_token_expires_at',
            ]);
        });
    }
};
