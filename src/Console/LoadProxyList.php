<?php

namespace App\Console;

use App\Proxy\ParserInterface;
use App\Repository\ProxyRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoadProxyList extends Command
{
    public function __construct(
        private readonly ProxyRepository $proxyRepository,
        private readonly ParserInterface $parser,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('proxy-list:load');
        $this->setDescription('Parse proxy list');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->proxyRepository->truncate();
            $parsedProxies = $this->parser->parse();
            array_walk($parsedProxies, $this->proxyRepository->save(...));

            $output->writeln(sprintf('Parsed %d proxies', count($parsedProxies)));
        } catch (\Throwable $e) {
            $output->writeln($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
