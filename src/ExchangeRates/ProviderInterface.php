<?php

namespace App\ExchangeRates;

use App\Entity\ExchangeRate;

interface ProviderInterface
{
    /**
     * @return iterable<ExchangeRate>
     */
    public function get(): iterable;
}
