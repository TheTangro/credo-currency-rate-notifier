<?php

namespace App\Console;

use App\Proxy\ParserInterface;
use App\Repository\ProxyRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LoadProxyList extends Command
{
    public function __construct(
        private readonly ProxyRepository $proxyRepository,
        private readonly ParserInterface $parser,
        private readonly LoggerInterface $logger
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
        $io = new SymfonyStyle($input, $output);

        try {
            $parsedProxies = $this->parser->parse();
            $parsedCount = $this->proxyRepository->refillTable($parsedProxies);

            $this->logger->info(sprintf('Parsed %d proxies', $parsedCount));
            $io->success(sprintf('Parsed %d proxies', $parsedCount));
        } catch (\Throwable $e) {
            $io->error($e->getMessage());
            $this->logger->error(sprintf('%s%s%s', $e->getMessage(), PHP_EOL, $e->getTraceAsString()));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
