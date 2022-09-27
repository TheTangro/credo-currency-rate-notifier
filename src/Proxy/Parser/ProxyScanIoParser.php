<?php

namespace App\Proxy\Parser;

use App\Proxy\ParserInterface;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class ProxyScanIoParser implements ParserInterface
{
    private ?Client $client = null;

    public function parse(): array
    {
        $guzzleClient = $this->getClient();
    }

    private function getClient(): Client
    {
        if ($this->client === null) {
            $this->client = new \GuzzleHttp\Client([
                'base_uri' => 'https://www.proxyscan.io/',
                RequestOptions::CONNECT_TIMEOUT => 300
            ]);
        }

        return $this->client;
    }
}
