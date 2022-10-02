<?php

namespace App\Notifications\Notifiers;

use App\Notifications\NotifierInterface;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class TelegramNotifier implements NotifierInterface
{
    private ?Client $client = null;

    public function __construct(
        private $botToken,
        private $chatId
    ) {
    }

    public function notify(string $message): void
    {
        $client = $this->getGuzzleClient();
        $client->post(
            sprintf('/bot%s/sendMessage', $this->botToken),
            [
                'query' => [
                    'chat_id' => $this->chatId,
                    'text' => $message
                ]
            ]
        );
    }

    private function getGuzzleClient(): Client
    {
        if ($this->client === null) {
            $this->client = new Client([
                'base_uri' => 'https://api.telegram.org/',
                RequestOptions::CONNECT_TIMEOUT => 300
            ]);
        }

        return $this->client;
    }
}
