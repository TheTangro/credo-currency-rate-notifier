<?php

namespace App\Notifications;

class Notification
{
    public function __construct(
        private readonly string $message,
        private readonly string $source
    ) {
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }
}
