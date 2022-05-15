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
        Schema::create('beers', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->timestamps();
        });

        $beerMessages = [
            'Налил пинту кофейного стаута',
            'Налил кружку янтарного эля',
            'Налил стакан IPA',
            'Налил кислого жигулевского в банку семисотку',
            'Налил литр русского имперского стаута. Прощай. ;-)',
            'Налил бокал ледяного пшеничного',
            'Налил балтики 9 тебе прямо в рот',
            'Налил томатного GOZE с чесноком в чайную чашку с цветочками',
            'Налил стакан блонд эля',
            'Налил портера в жестяную кружку',
            'Налил лагера из-под крана'
        ];

        foreach ($beerMessages as $msg) {
            \App\Models\Beer::create(
                [
                    'content' => $msg
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('beers');
    }
};
