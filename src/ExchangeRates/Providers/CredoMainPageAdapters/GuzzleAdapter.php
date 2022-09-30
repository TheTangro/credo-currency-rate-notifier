<?php

namespace App\ExchangeRates\Providers\CredoMainPageAdapters;

use App\Exceptions\UnableToLoadExchangeRateException;
use App\Repository\ProxyRepository;
use Faker\Provider\UserAgent as UserAgentGenerator;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class GuzzleAdapter implements AdapterInterface
{
    private ?ClientInterface $client = null;

    public function __construct(
        private readonly ContainerBagInterface $containerBag,
        private readonly LoggerInterface $logger,
        private readonly ProxyRepository $proxyRepository
    ) {
    }

    public function getHtml(): string
    {
        $maxTries = $this->containerBag->get('notifier.guzzle.exchange_rates.tries_amount') ?: 3;

        for ($tryNumber = 0; $tryNumber <= $maxTries; $tryNumber++) {
            $guzzleClient = $this->getGuzzleClient();
            $proxy = $this->proxyRepository->getLeastUsed();
            $proxyStats = $proxy->getStats();
            $proxyStats->setUsageCounter($proxyStats->getUsageCounter() + 1);
            $proxyLine = sprintf(
                '%s://%s:%d',
                mb_strtolower($proxy->getType()),
                $proxy->getIp()->getDotAddress(),
                $proxy->getPort()
            );

            try {
                $result = $guzzleClient->get(
                    '/en/exchange-rates/',
                    [
                        'headers' => [
                            'User-Agent' => UserAgentGenerator::userAgent()
                        ],
                        'proxy' => $proxyLine,
                        'http_errors' => true,
                        'verify' => false
                    ]
                );
            } catch (\Throwable $e) {
                $proxyStats->setUsageCounter($proxyStats->getErrorsCounter() + 1);
                $this->logger->emergency($e->getMessage());
            }

            $this->proxyRepository->save($proxy, true);

            if (isset($result) && $result->getStatusCode() === 200) {
                return $result->getBody()->getContents();
            }
        }

        throw new UnableToLoadExchangeRateException('Unable to load exchange rates');
    }

    private function getGuzzleClient(): ClientInterface
    {
        if ($this->client === null) {
            $this->client = new Client([
                'base_uri' => 'https://credobank.ge/',
                RequestOptions::CONNECT_TIMEOUT => $this->containerBag->get('notifier.guzzle.timeout')
            ]);
        }

        return $this->client;
    }
}
