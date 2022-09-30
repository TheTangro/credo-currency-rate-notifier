<?php

namespace App\Console;

use App\ExchangeRates\ProviderInterface as ExchangeRateProvider;
use App\Repository\ExchangeRateRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LoadExchangeRates extends Command
{
    public function __construct(
        private readonly ExchangeRateProvider $exchangeRateProvider,
        private readonly LoggerInterface $logger,
        private readonly ExchangeRateRepository $exchangeRateRepository,
        private readonly ManagerRegistry $managerRegistry
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('exchange-rates:load');
        $this->setDescription('Parse exchange rates');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var \Doctrine\DBAL\Connection $connection **/
        $connection = $this->managerRegistry->getConnection();
        $io = new SymfonyStyle($input, $output);
        $connection->beginTransaction();

        try {
            foreach ($this->exchangeRateProvider->get() as $exchangeRate) {
                $this->exchangeRateRepository->save($exchangeRate, true);
            }

            $connection->commit();
            $this->logger->info(sprintf('Successfully parsed exchange rates %s', date('d-m-Y h:i:s')));
            $io->success(sprintf('Successfully parsed exchange rates %s', date('d-m-Y h:i:s')));
        } catch (\Throwable $e) {
            $connection->rollBack();
            $this->logger->error(sprintf('%s%s%s', $e->getMessage(), PHP_EOL, $e->getTraceAsString()));
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
