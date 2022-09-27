<?php

namespace App\Proxy;

use App\Entity\Proxy;

interface ParserInterface
{
    /**
     * @return \Generator<Proxy>
     */
    public function parse(): \Generator;
}
