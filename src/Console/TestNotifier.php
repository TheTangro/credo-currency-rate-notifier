<?php

namespace App\Console;

use App\Notifications\NotifierInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestNotifier extends Command
{
    public function __construct(
        private readonly NotifierInterface $notifier
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('test:notifier');
        $this->setDescription('Test Notifier');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->notifier->notify('Test notification sended by notifier:test');

        return self::SUCCESS;
    }
}
