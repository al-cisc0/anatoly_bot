<?php

use App\Models\Chat;
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
        Schema::create('reactions', function (Blueprint $table) {
            $table->id();
            $table->text('key_phrase');
            $table->json('response');
            $table->boolean('is_ai_based')->default(false);
            $table->boolean('is_array')->default(false);
            $table->boolean('is_strict')->default(false);
            $table->boolean('is_class_trigger')->default(false);
            $table->boolean('is_daily_updated')->default(false);
            $table->foreignIdFor(Chat::class)
                  ->nullable()
                  ->constrained()
                  ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reactions');
    }
};
