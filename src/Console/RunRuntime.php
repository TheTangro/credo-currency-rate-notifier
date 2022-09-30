<?php

namespace App\Console;

use App\Utils\Stopwatch;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;

class RunRuntime extends Command
{
    public function __construct(
        private readonly ContainerBagInterface $containerBag,
        private readonly LoggerInterface $logger,
        private readonly KernelInterface $kernel
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('runtime:execute');
        $this->setDescription('Start runtime');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Stopwatch[] $stopwatches **/
        $stopwatches = [
            Stopwatch::setInterval(
                $this->runRenewProxy(...),
                $this->containerBag->get('notifier.proxy.renew_proxies_gap')
            ),
            Stopwatch::setInterval(
                $this->runLoadExchangeRates(...),
                $this->containerBag->get('notifier.exchange_rates.load_rates_gap')
            )
        ];

        while (true) {
            foreach ($stopwatches as $stopwatch) {
                $stopwatch->renew();
            }

            sleep(1);
        }
    }

    private function runRenewProxy(): void
    {
        $this->runCommand('proxy-list:load');
    }

    private function runLoadExchangeRates(): void
    {
        $this->runCommand('exchange-rates:load');
    }

    private function getLockFile(string $name): string
    {
        $name = preg_replace('/^.*::/', '', $name);
        $name = mb_strtolower($name) . '.lock';
        $lockDir = $this->containerBag->get('runtime.lock_dir');
        @mkdir($lockDir, 0744, true);
        $lockFile = $lockDir . DIRECTORY_SEPARATOR . $name;

        if (!file_exists($lockFile)) {
            file_put_contents($lockFile, '');
        }

        return $lockFile;
    }

    private function runCommand(string $name, string $argsString = '')
    {
        $projectDir = $this->kernel->getProjectDir();
        $executeString = sprintf(
            '%s -n %s %s %s/bin/console %s %s &>/dev/null &',
            '/usr/bin/flock',
            $this->getLockFile(__METHOD__),
            '/usr/bin/php',
            $projectDir,
            $name,
            $argsString
        );
        proc_open($executeString, [], $pipes, $projectDir, $_ENV ?? []);
    }
}
