<?php

namespace App\Proxy\Parser;

use App\Entity\Proxy;
use App\Entity\ProxyStats;
use App\Exceptions\ProxyParsingException;
use App\Proxy\ParserInterface;
use Darsyn\IP\Version\Multi;
use DiDom\Document;
use DiDom\Element;
use Faker\Provider\UserAgent as UserAgentGenerator;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class ProxyScanIoParser implements ParserInterface
{
    private ?Client $client = null;

    private array $parsed = [];

    public function __construct(
        private readonly ContainerBagInterface $containerBag
    ) {
    }

    /**
     * @return \Generator<Proxy>
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function parse(): \Generator
    {
        $parsedProxies = [];

        foreach ($this->loadProxies() as $loadProxy) {
            $parsedProxies[] = $loadProxy;
        }

        usort($parsedProxies, function (array $proxyNodeA, array $proxyNodeB) {
            $availabilityA = (int) $proxyNodeA['availability'];
            $availabilityB = (int) $proxyNodeB['availability'];

            return $availabilityB <=> $availabilityA;
        });
        $parsedProxies = array_slice(
            $parsedProxies,
            0,
            $this->containerBag->get('notifier.proxy.load.amount')
        );

        foreach ($parsedProxies as $proxyData) {
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
                        'User-Agent' => UserAgentGenerator::userAgent()
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
                $availabilityNode = $proxyNode->find('.progress-bar[role="progressbar"]');

                if (empty($availabilityNode)) {
                    throw new ProxyParsingException('Invalid proxy parsing. Unable to parse availability');
                }

                $availability = $this->extractAvailability(reset($availabilityNode));

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
                        'ip' => $ip,
                        'availability' => $availability
                    ];
                }
            }
        }

        return $parsedNodes;
    }

    private function extractAvailability(Element $element): int
    {
        $styleNode = (string) $element->getAttribute('style');
        preg_match('/(?<=width:)\d+(?=%)/m', $styleNode, $matches);

        return !empty($matches) ? (int) reset($matches) : 0;
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
