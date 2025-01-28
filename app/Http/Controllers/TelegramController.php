<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Api;
use App\Models\User;
use Telegram\Bot\Keyboard\Keyboard;

class TelegramController extends Controller
{
    protected $telegram;

    public function __construct()
    {
        $this->telegram = new Api(config('TELEGRAM_BOT_TOKEN'));
    }

    public function webhook(Request $request)
    {
        $update = $this->telegram->getWebhookUpdate();
        $chatId = $update->getMessage()->getChat()->getId();
        $username = $update->getMessage()->getFrom()->getUsername();
        $userId = $update->getMessage()->getFrom()->getId();
        $text = $update->getMessage()->getText();
        $file = $update->getMessage()->getDocument();
        $photo = $update->getMessage()->getPhoto();

        $user = User::firstOrCreate(['telegram_id' => $userId, 'name' => $username]);
        if($user->status = 'kicked')
        {
            $user->status = 'active';
            $user->save();
        }

        if ($file) {
            $this->handleFileMessage($chatId, $file);
        }
        elseif ($photo) {
            $this->handlePhotoMessage($chatId, $photo);
        }
        else {
            $this->handleTextMessage($chatId, $userId, $text, $username, $user);
        }
    }

    protected function handleTextMessage($chatId, $userId, $text, $username, $user)
    {
//        $user = User::firstOrCreate(['telegram_id' => $userId, 'name' => $username]);

        if ($text === 'Сколько нажатий?') {
            $user->increment('clicks');

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "Количество нажатий на кнопку: " . $user->clicks,
            ]);
            return;
        }

        $reply_markup = Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button('Сколько нажатий?'),
            ]);

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "Ваш Telegram ID: $userId\nОтправленный текст: $text",
            'reply_markup' => $reply_markup,
        ]);
    }

    protected function handleFileMessage($chatId, $file)
    {
        $fileName = $file->getFileName();
        $fileSize = $file->getFileSize();

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "Имя файла: $fileName\nРазмер файла: $fileSize байт",
        ]);
    }

    protected function handlePhotoMessage($chatId, $photo)
    {
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "Получено изображение",
        ]);
    }

    public function sendBroadcast(Request $request)
    {
        $message = $request->input('message');
        $users = User::all();

        foreach ($users as $user) {
            try {
                $this->telegram->sendMessage([
                    'chat_id' => $user->telegram_id,
                    'text' => $message,
                ]);
            } catch (\Telegram\Bot\Exceptions\TelegramResponseException $e) {
                if ($e->getMessage() === 'Forbidden: bot was blocked by the user') {
                    $user->status = 'kicked';
                    $user->save();
                }
            }
        }

        return redirect()->back()->with('success', 'Сообщения успешно отправлены!');
    }
}
