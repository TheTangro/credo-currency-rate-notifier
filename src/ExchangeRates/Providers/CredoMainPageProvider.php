<?php

namespace App\ExchangeRates\Providers;

use App\Api\CurrencyCodes;
use App\Api\ExchangeRatesTypes;
use App\Entity\ExchangeRate;
use App\Exceptions\UnableToLoadExchangeRateException;
use App\ExchangeRates\ProviderInterface;
use App\ExchangeRates\Providers\CredoMainPageAdapters\AdapterInterface;
use DiDom\Document;
use DiDom\Element;

class CredoMainPageProvider implements ProviderInterface
{
    public const TYPES_MAP = [
        'credo' => ExchangeRatesTypes::COMMERCIAL_RATE,
        'credo_transfer' => ExchangeRatesTypes::TRANSFER_RATE,
        'credo_card' => ExchangeRatesTypes::AUTOMATIC_OPERATION_RATE,
        'nbg' => ExchangeRatesTypes::NATIONAL_BANK_RATE
    ];

    public function __construct(
        private readonly AdapterInterface $credoMainPageLoaderAdapter
    ) {
    }

    public function get(): iterable
    {
        foreach ($this->parseData() as $exchangeRate) {
            $exchangeRateObject = $this->convertDataToModel($exchangeRate);

            yield $exchangeRateObject;
        }
    }

    /**
     * @return string[][]
     */
    private function parseData(): array
    {
        $html = $this->credoMainPageLoaderAdapter->getHtml();
        $rates = $this->extractData($html);

        return $rates;
    }

    private function extractData(string $html): array
    {
        $document = new Document($html);
        $currencyNodes = $document->find('.rate-input[data-target-type][data-currency]');
        $result = [];

        if (empty($currencyNodes)) {
            throw new UnableToLoadExchangeRateException('Credo bank response does not contain currency rates');
        }

        foreach ($currencyNodes as $currencyNode) {
            [$type, $exchangeRates] = $this->extractDataFromHtmlNode($currencyNode);

            if (!array_key_exists($type, self::TYPES_MAP)) {
                throw new UnableToLoadExchangeRateException('Invalid credo rate type');
            } else {
                $type = self::TYPES_MAP[$type];
            }

            if (count($exchangeRates) !== 2) {
                throw new UnableToLoadExchangeRateException('Invalid credo data');
            } else {
                [$buyRates, $sellRates] = $exchangeRates;
            }

            foreach ($buyRates as $currency => $exchangeRate) {
                if ($currency === CurrencyCodes::GEL->name) {
                    continue;
                }

                if (!isset($sellRates[$currency])) {
                    throw new UnableToLoadExchangeRateException('Unable to find sell rate for buyRate');
                }

                $result[] = [
                    'currency' => $currency,
                    'buyRate' => $exchangeRate,
                    'sellRate' => $sellRates[$currency],
                    'type' => $type
                ];
            }
        }

        return $result;
    }

    private function extractDataFromHtmlNode(Element $currencyNode): array
    {
        $type = $currencyNode->getAttribute('data-target-type');
        $exchangeRatesJson = $currencyNode->getAttribute('data-currency');

        try {
            $exchangeRatesJson = preg_replace(
                '/(\"\w+\":\s*?)(\d+\.?[^,\}]*\b)/imu',
                '$1"$2"',
                $exchangeRatesJson
            );
            $exchangeRates = json_decode($exchangeRatesJson, true);
        } catch (\Throwable $e) {
            $exchangeRates = [];
        }

        return [$type, $exchangeRates];
    }

    private function convertDataToModel(array $data): ExchangeRate
    {
        extract($data);
        $exchangeRate = new ExchangeRate();
        $exchangeRate->setSellRate($sellRate);
        $exchangeRate->setBuyRate($buyRate);
        $exchangeRate->setCurrencyCode($currency);
        $exchangeRate->setExchangeDate(new \DateTimeImmutable());
        $exchangeRate->setExchangeType($type->name);

        return $exchangeRate;
    }
}
