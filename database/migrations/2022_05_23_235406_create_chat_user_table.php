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
        Schema::create('chat_user', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Chat::class)
                ->constrained()
                ->onDelete('cascade');
            $table->foreignIdFor(\App\Models\User::class)
                ->constrained()
                ->onDelete('cascade');
            $table->boolean('is_admin')
                ->default(0);
            $table->bigInteger('rating')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_user');
    }
};
