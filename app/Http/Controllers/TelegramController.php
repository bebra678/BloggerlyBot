<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Api;
use App\Models\User;

class TelegramController extends Controller
{
    protected $telegram;

    public function __construct()
    {
        $this->telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
    }

    public function webhook(Request $request)
    {
        $update = $this->telegram->getWebhookUpdate();
        $chatId = $update->getMessage()->getChat()->getId();
        $messageText = $update->getMessage()->getText();
        $file = $update->getMessage()->getDocument();

        $user = User::firstOrCreate(['telegram_id' => $chatId]);

        if ($messageText) {
            $user->increment('clicks');
            $user->save();

            $replyMarkup = json_encode([
                'inline_keyboard' => [
                    [
                        [['text' => 'Количество нажатий', 'callback_data' => 'get_clicks']]
                    ]
                ]
            ]);

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "Ваш ID: $chatId\nВаше сообщение: $messageText",
                'reply_markup' => $replyMarkup
            ]);
        }

        if ($file) {
            $fileName = $file->getFileName();
            $fileSize = $file->getFileSize();

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "Имя файла: $fileName\nРазмер файла: $fileSize байт"
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    public function callback(Request $request)
    {
        $callbackData = $request->input('callback_query.data');
        $chatId = $request->input('callback_query.from.id');

        if ($callbackData === 'get_clicks') {
            $user = User::where('telegram_id', $chatId)->first();
            $clicks = $user ? $user->clicks : 0;

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "Количество нажатий: $clicks"
            ]);
        }

        return response()->json(['status' => 'ok']);
    }
}
