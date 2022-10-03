<?php

namespace App\ExchangeRates\Checkers;

use App\Api\CurrencyCodes;
use App\Api\ExchangeRatesTypes;
use App\ExchangeRates\CheckerInterface;
use App\Notifications\Notification;
use App\Repository\ExchangeRateRepository;
use Psr\Log\LoggerInterface;

class EuroHigherUSDChecker implements CheckerInterface
{
    public function __construct(
        private readonly ExchangeRateRepository $exchangeRateRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function check(): ?Notification
    {
        $latestEuroRate = $this->exchangeRateRepository->getLatestRateByCodeAndType(
            CurrencyCodes::EUR,
            ExchangeRatesTypes::TRANSFER_RATE
        );
        $latestUsdRate = $this->exchangeRateRepository->getLatestRateByCodeAndType(
            CurrencyCodes::USD,
            ExchangeRatesTypes::TRANSFER_RATE
        );

        $euroUsdRatio = bcdiv($latestEuroRate->getBuyRate(), $latestUsdRate->getSellRate(), 72);
        $ratio = number_format($euroUsdRatio, 4, '.');

        if (bccomp($euroUsdRatio, '1', 72) >= 1) {
            $this->logger->info(sprintf('Found EUR/USD ratio greater than 1 (%s)', (string) $ratio));
            $notification = new Notification(
                sprintf(
                    'EUR / USD ratio is greater than 1.'. PHP_EOL
                    . 'Ratio = %s' . PHP_EOL
                    . 'Exhange rate of EUR has been loaded at: %s' . PHP_EOL
                    . 'Exhange rate of USD has been loaded at: %s',
                    $ratio,
                    $latestEuroRate->getExchangeDate()->format('d-m-Y H:i:s'),
                    $latestUsdRate->getExchangeDate()->format('d-m-Y H:i:s')
                ),
                static::class
            );
        } else {
            $this->logger->debug(sprintf('EUR/USD ratio is lower than 1. Ratio = %s', $ratio));
        }

        return  $notification ?? null;
    }
}
