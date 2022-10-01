<?php

namespace App;

use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function boot()
    {
        parent::boot();

        $this->registerPgLogger();
    }

    private function registerPgLogger(): void
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $connection = $em->getConnection()->getNativeConnection();
        $pgHandler = new \PGHandler\PGHandler($connection, 'system_log');
        $logger = $this->getContainer()->get('logger');
        /** @var Logger $logger * */
        $logger->pushHandler($pgHandler);
    }
}
