<?php

namespace App\Notifications;

interface NotifierInterface
{
    public function notify(string $message): void;
}
