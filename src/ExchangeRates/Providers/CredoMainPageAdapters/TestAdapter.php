<?php

namespace App\ExchangeRates\Providers\CredoMainPageAdapters;

use App\Exceptions\UnableToLoadExchangeRateException;
use Symfony\Component\HttpKernel\KernelInterface;

class TestAdapter implements AdapterInterface
{
    public function __construct(
        private readonly KernelInterface $appKernel
    ) {
    }

    public function getHtml(): string
    {
        $path = $this->appKernel->getProjectDir() . DIRECTORY_SEPARATOR
            . 'mock' . DIRECTORY_SEPARATOR . 'credo_response';

        if (file_exists($path)) {
            return file_get_contents($path);
        }

        throw new UnableToLoadExchangeRateException('Unable to load exchange rates');
    }
}
