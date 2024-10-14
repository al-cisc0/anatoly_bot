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
        Schema::table('chats', function (Blueprint $table) {
            $table->text('captcha_question')
                  ->nullable()
                  ->after('is_spam_detection_enabled');
            $table->text('captcha_answer')
                  ->nullable()
                  ->after('captcha_question');
            $table->boolean('is_captcha_enabled')
                  ->default(false)
                  ->after('is_spam_detection_enabled');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropColumn('captcha_question');
            $table->dropColumn('captcha_answer');
            $table->dropColumn('is_captcha_enabled');
        });
    }
};
