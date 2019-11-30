<?php


namespace App\Services\Common;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TelegramInformer
{
    private $telegram_token;
    private $telegram_user_id;
    private $http_client;

    public function __construct(HttpClientInterface $http_client, $tm_token, $tm_user_id)
    {
        $this->telegram_token = $tm_token;
        $this->telegram_user_id = $tm_user_id;
        $this->http_client = $http_client;
    }

    public function messageToTelegram($text)
    {

        $response = $this->http_client->request('POST', 'https://api.telegram.org/bot' . $this->telegram_token . '/sendMessage', [
            'query' => [
                'chat_id' => $this->telegram_user_id,
                'text' => $text,
            ],
        ]);
    }
}
