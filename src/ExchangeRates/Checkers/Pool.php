<?php

namespace App\ExchangeRates\Checkers;

use App\ExchangeRates\CheckerInterface;
use Traversable;

class Pool implements \IteratorAggregate
{
    private array $checkers = [];

    /**
     * @param array<CheckerInterface> $checkers
     */
    public function __construct(
        array $checkers = []
    ) {
        $this->checkers = $checkers;
    }

    /**
     * @return Traversable<CheckerInterface>
     */
    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->checkers);
    }
}
