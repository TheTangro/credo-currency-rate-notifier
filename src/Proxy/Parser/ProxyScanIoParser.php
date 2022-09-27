<?php

namespace App\Proxy\Parser;

use App\Entity\Proxy;
use App\Entity\ProxyStats;
use App\Proxy\ParserInterface;
use Darsyn\IP\Version\Multi;
use DiDom\Document;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class ProxyScanIoParser implements ParserInterface
{
    private ?Client $client = null;

    public const USER_AGENTS = [
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 12_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Safari/605.1.15',
        'Mozilla/5.0 (iPad; CPU OS 16_0_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1',
        'Mozilla/5.0 (iPod touch; CPU iPhone 16_0_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0 Safari/605.1.15'
    ];

    private array $parsed = [];

    /**
     * @return \Generator<Proxy>
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function parse(): \Generator
    {
        foreach ($this->loadProxies() as $proxyData) {
            $proxy = new Proxy();
            extract($proxyData);
            $cacheKey = $ip . $port;

            if (!array_key_exists($cacheKey, $this->parsed)) {
                $proxy->setIp(Multi::factory($ip));
                $proxy->setType($type);
                $proxy->setPort($port);
                $proxy->setLocation($location);
                $proxy->setStats(new ProxyStats());

                yield $proxy;
            }
        }
    }

    /**
     * @return \Generator
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function loadProxies(): \Generator
    {
        $guzzleClient = $this->getClient();
        $page = 0;

        do {
            $response = $guzzleClient->get('/home/filterresult',
                [
                    'query' => sprintf(
                        'limit=100&page=%d&status=1&ping=&selectedType=HTTPS&selectedType=SOCKS4'
                        . '&selectedType=SOCKS5&SelectedAnonymity=Anonymous&SelectedAnonymity=Elite'
                        . '&sortPing=false&sortTime=true&sortUptime=false&_=%d',
                        $page++,
                        time()
                    ),
                    'headers' => [
                        'User-Agent' => self::USER_AGENTS[rand(0, count(self::USER_AGENTS) - 1)]
                    ]
                ]
            );
            $entities = $this->parseResponse($response->getBody()->getContents());
            $proceed = !empty($entities);

            yield from $entities;
        } while ($proceed);
    }

    private function parseResponse(string $html): array
    {
        $parsedNodes = [];

        if (!empty($html)) {
            $document = new Document($html);
            $proxyNodes = $document->find('tr');

            foreach ($proxyNodes as $proxyNode) {
                $ip = trim($proxyNode->first('th')->text());
                $additionalData = $proxyNode->find('td');

                if (empty($ip) || !isset($additionalData[0], $additionalData[1], $additionalData[3])) {
                    continue;
                } else {
                    $port = intval(trim($additionalData[0]->text()));
                    $location = trim($additionalData[1]->text());
                    $types = array_map('trim', explode(',', trim($additionalData[3]->text())));
                    $type = end($types);

                    $parsedNodes[] = [
                        'port' => $port,
                        'location' => $location,
                        'type' => $type,
                        'ip' => $ip
                    ];
                }
            }
        }

        return $parsedNodes;
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
