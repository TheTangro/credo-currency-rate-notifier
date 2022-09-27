<?php

namespace App\Proxy;

use App\Entity\Proxy;

interface ParserInterface
{
    /**
     * @return Proxy[]
     */
    public function parse(): array;
}
