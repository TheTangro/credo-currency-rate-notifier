<?php

namespace App\Utils;

class Stopwatch
{
    private int $seconds;

    private \Closure $callback;

    private array $arguments;

    private int $startedSeconds;

    protected function __construct()
    {
    }

    public static function setInterval(callable $callback, int $seconds, ...$arguments): static
    {
        $instance = new static();
        $instance->seconds = $seconds;
        $instance->callback = $callback(...);
        $instance->arguments = $arguments;
        $instance->startedSeconds = time();

        return $instance;
    }

    public function renew(): void
    {
        $currentTime = time();
        $callTime = $this->startedSeconds + $this->seconds;

        if ($currentTime >= $callTime) {
            $this->startedSeconds = $currentTime;
            $callback = $this->callback;
            $callback(...$this->arguments);
        }
    }
}
