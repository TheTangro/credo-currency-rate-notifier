<?php

namespace App\ExchangeRates;

use App\Notifications\Notification;

interface CheckerInterface
{
    public function check(): ?Notification;
}
