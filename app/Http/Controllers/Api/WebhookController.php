<?php

namespace App\Http\Controllers\Api;

use App\CrawlerExtracts\BooksExtract;
use App\CrawlerExtracts\ChapayExtract;
use App\CrawlerExtracts\GaricExtract;
use App\CrawlerExtracts\PickupMasterExtract;
use App\Http\Controllers\Controller;
use App\Models\Beer;
use App\Models\Chat;
use App\Models\User;
use App\Notifications\SimpleBotMessageNotification;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WebhookController extends Controller
{

    /**
     * User who interacts with bot
     *
     * @var null
     */
    protected $user = null;

    protected $chat = null;

    /**
     * Incoming message array
     *
     * @var array
     */
    protected $message = [];

    /**
     * Handle bot webhook and decide what to do next
     *
     * @param Request $request
     * @param string $token
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function setBotInput(Request $request, string $token)
    {
        if ($token != config('services.telegram-bot-api.token')) {
            return abort(403);
        }
        $this->message = $request->get('message');
        $this->setUser();
        if ($this->message) {
            $this->parseMessage();
        }
        return response()->json([]);
    }

    protected function parseMessage()
    {
        $text = mb_strtolower($this->message['text'] ?? '');
        if (
            str_contains(
                $text,
                'анатолий'
            )) {
            $this->sendBotResponse(new SimpleBotMessageNotification(
                                       'Я читаю все ваши сообщения и реагирую на слова: Анатолий, пиво, поболтаем, гарик, мачо, правила, заправки, заправка (можно с номером), попрошайка, книга, анекдот, линейка. А еще я приветствую всех кто присоединяется к чату.',
                                       $this->message
                                   ));
        }
        if (str_contains(
            $text,
            'поболтаем'
        )
        ) {
            $this->sendBotResponse(new SimpleBotMessageNotification(
                'Могу поболтать своей электронной ялдой',
                $this->message
                                   ));
        }
        if (str_contains(
            $text,
            'дикпик'
        )
        ) {
            $this->sendBotResponse(new SimpleBotMessageNotification(
                $this->getPinus(),
                $this->message
                                   ));
        }
        if (
            str_contains(
                $text,
                'пиво'
            ) ||
            str_contains(
                $text,
                'пива'
            )
        ) {
            $beer = Beer::inRandomOrder()->first();
            $this->sendBotResponse(new SimpleBotMessageNotification(
                                       $beer->content,
                                       $this->message
                                   ));
        }
        if ( str_contains(
            $text,
            'гарик'
        )) {
            $this->sendBotResponse(new SimpleBotMessageNotification(
                                       GaricExtract::getExtract(),
                                       $this->message
                                   ));
        }
        if ( str_contains(
            $text,
            'линейк'
        )) {
            $this->sendBotResponse(new SimpleBotMessageNotification(
                                       $this->getSize(),
                                       $this->message
                                   ));
        }
        if ( str_contains(
            $text,
            'анекдот'
        )) {
            $this->sendBotResponse(new SimpleBotMessageNotification(
                                       ChapayExtract::getExtract(),
                                       $this->message
                                   ));
        }
        if ( str_contains(
            $text,
            'мачо'
        )) {
            $this->sendBotResponse(new SimpleBotMessageNotification(
                                       PickupMasterExtract::getExtract(),
                                       $this->message
                                   ));
        }
        if ( str_contains(
            $text,
            'книг'
        )) {
            $this->sendBotResponse(new SimpleBotMessageNotification(
                                       BooksExtract::getExtract(),
                                       $this->message
                                   ));
        }
        if ( str_contains(
            $text,
            'попрошайка'
        )) {
            $this->sendBotResponse(new SimpleBotMessageNotification(
                                       'Помочь на содержание и развитие Анатолия можно по реквизитам: '.PHP_EOL.PHP_EOL.config('bot.donation_address'),
                                       $this->message
                                   ));
        }
        if ( str_contains(
            $text,
            'правила'
        )) {
            $text = 'В этом чате пока что анархия наскорлько я знаю.';
            if ($this->chat?->rules) {
                $text = 'Правила чата: '.PHP_EOL.PHP_EOL.$this->chat->rules;
            }
            $this->sendBotResponse(new SimpleBotMessageNotification(
                                       $text,
                                       $this->message
                                   ));
        }
        if ( str_contains(
            $text,
            'заправки'
        )) {
            $this->sendBotResponse(new SimpleBotMessageNotification(
                                        $this->getDressings(
                                            $text,
                                            false
                                        ),
                                       $this->message
                                   ));
        }
        if ( str_contains(
            $text,
            'заправка'
        )) {
            $this->sendBotResponse(new SimpleBotMessageNotification(
                                       $this->getDressings(
                                           $text
                                       ),
                                       $this->message
                                   ));
        }
        if (!empty($this->message['new_chat_participant']['is_bot']) && $this->message['new_chat_participant']['is_bot'] == 1) {
            $this->sendBotResponse(new SimpleBotMessageNotification(
                'Эй ты, ублюдок электрический, ну-ка укажи все светофоры!',
                $this->message
                                   ));
        }
        if (!empty($this->message['new_chat_participant']) &&
            (
                empty($this->message['new_chat_participant']['is_bot']) ||
                $this->message['new_chat_participant']['is_bot'] == 0)
        ) {
            if ($this->chat?->rules) {
                $text = 'почитай правила чата: '.PHP_EOL.PHP_EOL.$this->chat->rules;
            } else {
                $text = 'расскажи куда путь держишь.';
            }
            $this->sendBotResponse(new SimpleBotMessageNotification(
                'Приветствую тебя, путник. Присаживайся, отдохни, выпей чаю и '.$text,
                $this->message
                                   ));

        }
    }

    protected function getSize()
    {
        $size = rand(1,40);
        $pinus = '8';
        for ($i = 1;$i<=$size;$i++) {
            $pinus .= '=';
        }
        $pinus .= 'Э';
        if ($size < 3) {
            $ranking = 'Гномик';
        }
        if ($size >= 3) {
            $ranking = 'Шалун';
        }
        if ($size >= 6) {
            $ranking = 'Затейник';
        }
        if ($size >= 10) {
            $ranking = 'Самурай';
        }
        if ($size >= 14) {
            $ranking = 'Рядовой';
        }
        if ($size >= 20) {
            $ranking = 'Лысый из бразерс';
        }
        if ($size >= 25) {
            $ranking = 'Мутант';
        }
        if ($size >= 30) {
            $ranking = 'Фантаст';
        }
        return 'У тебя '.$size.' см. Твой ранг - '.$ranking.PHP_EOL.PHP_EOL.$pinus;
    }

    protected function getDressings(
        string $text,
        ?bool $single = true,
    )
    {

        $dressings = [
            '1. Лучше бы ты не вёл себя как самовлюблённый осёл и святоша, когда проповедуешь Мою макаронную благодать. Если другие люди не верят в Меня, в этом нет ничего страшного. Я не настолько самовлюблён, честно. Кроме того, речь идёт не об этих людях, так что не будем отвлекаться.',
            '2. Лучше бы ты не оправдывал Моим именем угнетение, порабощение, шинкование или экономическую эксплуатацию других, ну и сам понимаешь, вообще мерзкое отношение к окружающим. Я не требую жертв, чистота обязательна для питьевой воды, а не для людей.',
            '3. Лучше бы ты не судил людей по их внешнему виду, одежде, или по тому, как они говорят. Веди себя хорошо, ладно? Ах да, и вбей это в свою тупую башку: Женщина — это личность. Мужчина — это личность. А зануда — это всегда зануда. Никто из людей не лучше других, за исключением умения модно одеваться — извини уж, но Я одарил в этом смысле только женщин и лишь кое-кого из парней — тех, кто отличает пурпурный от пунцового.',
            '4. Лучше бы ты не позволял себе действий, неприемлемых для тебя самого или твоего добровольного и искреннего партнёра (достигшего допустимого возраста и душевной зрелости). Всем несогласным предлагаю идти лесом, если только они не считают это оскорбительным. В таком случае они могут для разнообразия выключить телевизор и пойти прогуляться.',
            '5. Лучше бы ты не боролся с фанатическими, женоненавистническими и другими злобными идеями окружающих на пустой желудок. Поешь, а потом иди к этим сволочам.',
            '6. Лучше бы ты не тратил уйму денег на постройку церквей, храмов, мечетей, усыпальниц во имя прославления Моей макаронной благодати, ведь эти деньги лучше потратить — выбирай, на что: - на прекращение бедности - на излечение болезней - на мирную жизнь, страстную любовь, и снижение стоимости Интернета. Пускай Я и сложноуглеводное всеведущее создание, но Я люблю простые радости жизни. Кому, как не мне знать? Ведь это Я всё создал.',
            '7. Лучше бы ты не рассказывал всем окружающим, как Я говорил с тобой. Ты не настолько всем интересен. Хватит думать только о себе. И помни, что Я попросил тебя любить своего ближнего, неужели не дошло?',
            '8. Лучше бы ты не поступал с другими так, как хочешь, чтобы поступили с тобой, если речь заходит об огромном количестве латекса или вазелина. Но если другому человеку это тоже нравится, то (следуя четвёртой заповеди) делай это, снимай на фото, только ради всего святого — надевай презерватив! Ведь это всего лишь кусок резины. Если бы Я не хотел, чтобы ты получал удовольствие от самого процесса, Я бы предусмотрел шипы или ещё что-нибудь в этом роде.',
        ];
        if ($single) {
            $matches = [];
            if (preg_match('/[\d]+/',$text,$matches)) {
                if (!empty($dressings[$matches[0]-1])) {
                    return $dressings[$matches[0]-1];
                }
            }
            return $dressings[array_rand($dressings)];
        }

        return implode(PHP_EOL.PHP_EOL,$dressings);
    }

    /**
     * Set current user who interacts with bot
     *
     */
    protected function setUser()
    {
        if (!empty($this->message['chat']['id'])) {
            $this->chat = Chat::firstOrCreate(
                [
                    'chat_id' => $this->message['chat']['id']
                ],
                [
                    'title' => !empty($this->message['chat']['title']) ?: '',
                    'rules' => '',
                ]
            );
        }
        if (!empty($this->message['from']['id'])) {
            $telegramId = $this->message['from']['id'];
            $userName = '';
            if (!empty($this->message['from']['first_name'])) {
                $userName .= $this->message['from']['first_name'];
            }
            if (!empty($this->message['from']['last_name'])) {
                $userName .= ' ' . $this->message['from']['last_name'];
            }
            $this->user = User::ofTelegramId($telegramId)
                              ->first();
            $isOwner = 0;
            $isActive = 0;
            if (config('bot.owner_id') == $telegramId) {
                $isOwner = 1;
                $isActive = 1;
            }
            if (!$this->user) {
                $this->user = User::create([
                                               'telegram_id' => $telegramId,
                                               'name'        => $userName,
                                               'is_active'   => $isActive,
                                               'is_admin'    => $isOwner,
                                               'password'    => Hash::make(Str::random(8))
                                           ]);
            }
        }
    }

    /**
     * Send response from bot to chat where command was executed
     *
     * @param Notification $notification
     */
    protected function sendBotResponse(Notification $notification)
    {
        $this->user->chat_id = $this->message['chat']['id'];
        $this->user->notify($notification);
    }

    public function test(Request $request)
    {
        print_r($request->all(),1);
    }

    protected function getPinus()
    {
        return '

.
..............▄▄ ▄▄▄
........▄▌▒▒▀▒▒▐▄
..... ▐▒▒▒▒▒▒▒▒▒▌
... ▐▒▒▒▒▒▒▒▒▒▒▒▌
....▐▒▒▒▒▒▒▒▒▒▒▒▌
....▐▀▄▄▄▄▄▄▄▄▄▀▌
....▐░░░░░░░░░░░▌
....▐░░░░░░░░░░░▌
....▐░░░░░░░░░░░▌
....▐░░░░░░░░░░░▌
....▐░░░░░░░░░░░▌
....▐░░░░░░░░░░░▌
....▐░░░░░░░░░░░▌
....▐░░░░░░░░░░░▌
....▐░░░░░░░░░░░▌
....▐░░░░░░░░░░░▌
....▐░░░░░░░░░░░▌
....▐░░░░░░░░░░░▌
....▐░░░░░░░░░░░▌
....▐░░░░░░░░░░░▌
....▐░░░░░░░░░░░▌
....▐░░░░░░░░░░░▌
....▐░░░░░░░░░░░▌
....▐░░░░░░░░░░░▌
....▐░░░░░░░░░░░▌
....▐░░░░░░░░░░░▌
....▐░░░░░░░░░░░▌
....▐░░░░░░░░░░░▌
....▐░░░░░░░░░░░▌
....▐░░░░░░░░░░░▌
....▐░░░░░░░░░░░▌
....▐░░░░░░░░░░░▌
....▐░░░░░░░░░░░▌
....▐░░░░░░░░░░░▌
....▐░░░░░░░░░░░▌
...▄█▓░░░░░░░░░▓█▄
..▄▀░░░░░░░░░░░░░ ▀▄
.▐░░░░░░░▀▄▒▄▀░░░░░░▌
▐░░░░░░░▒▒▐▒▒░░░░░░░▌
▐▒░░░░░▒▒▒▐▒▒▒░░░░░▒▌
.▀▄▒▒▒▒▒▄▀▒▀▄▒▒▒▒▒▄▀


';
    }
}
