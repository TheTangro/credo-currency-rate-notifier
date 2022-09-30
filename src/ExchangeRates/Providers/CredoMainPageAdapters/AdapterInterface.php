<?php

namespace App\ExchangeRates\Providers\CredoMainPageAdapters;

interface AdapterInterface
{
    /**
     * @return string
     *
     * @throws \App\Exceptions\UnableToLoadExchangeRateException
     */
    public function getHtml(): string;
}
