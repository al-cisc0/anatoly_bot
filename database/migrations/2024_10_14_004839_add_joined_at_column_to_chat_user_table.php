<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_user', function (Blueprint $table) {
            $table->timestamp('joined_at')
                  ->nullable()
                  ->after('user_id');
            $table->boolean('is_captcha_passed')
                  ->default(false)
                  ->after('joined_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chat_user', function (Blueprint $table) {
            $table->dropColumn('joined_at');
            $table->dropColumn('is_captcha_passed');
        });
    }
};
