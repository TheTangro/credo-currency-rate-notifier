<?php

namespace App\Console;

use App\ExchangeRates\Checkers\Pool as CheckersPool;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CheckersRun extends Command
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly CheckersPool $checkersPool,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('exchange-rates:checkers-run');
        $this->setDescription('Run checkers for exchange rates');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            foreach ($this->checkersPool as $checker) {
                $notification = $checker->check();

                if ($notification !== null) {
                    $this->messageBus->dispatch($notification);
                }
            }
        } catch (\Throwable $e) {
            $this->logger->critical(sprintf('%s\\n%s', $e->getMessage(), $e->getTraceAsString()));
        }

        return self::SUCCESS;
    }
}
