<?php

namespace App\Api;

enum ExchangeRatesTypes
{
    case COMMERCIAL_RATE;
    case TRANSFER_RATE;
    case AUTOMATIC_OPERATION_RATE;
    case NATIONAL_BANK_RATE;
}
