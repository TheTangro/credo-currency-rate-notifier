<?php

namespace App\ExchangeRates\Checkers;

use App\Api\CurrencyCodes;
use App\Api\ExchangeRatesTypes;
use App\ExchangeRates\CheckerInterface;
use App\Notifications\Notification;
use App\Repository\ExchangeRateRepository;
use Psr\Log\LoggerInterface;

class EuroEqualUSDChecker implements CheckerInterface
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
        $ratio = number_format($euroUsdRatio, 3, '.');

        if (bccomp($euroUsdRatio, '1.01', 72) < 1) {
            $this->logger->info(sprintf('Found EUR/USD ratio is equal than 1 (%s)', (string) $ratio));
            $notification = new Notification(
                sprintf(
                    'Found EUR/USD ratio is equal'. PHP_EOL
                    . 'Ratio = %s' . PHP_EOL,
                    $ratio,
                ),
                static::class
            );
        } else {
            $this->logger->debug(sprintf('EUR/USD ratio is not equal. Ratio = %s', $ratio));
        }

        return  $notification ?? null;
    }
}
