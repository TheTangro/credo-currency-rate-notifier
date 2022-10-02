<?php

namespace App\Repository;

use App\Api\CurrencyCodes;
use App\Api\ExchangeRatesTypes;
use App\Entity\ExchangeRate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExchangeRate>
 *
 * @method ExchangeRate|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExchangeRate|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExchangeRate[]    findAll()
 * @method ExchangeRate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExchangeRateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExchangeRate::class);
    }

    public function save(ExchangeRate $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ExchangeRate $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getLatestRateByCodeAndType(
        CurrencyCodes $code,
        ExchangeRatesTypes $type
    ): ?ExchangeRate {
        return $this->createQueryBuilder('p')
            ->andWhere('p.currencyCode = :currency_code')
            ->andWhere('p.exchangeType = :type')
            ->orderBy('p.exchangeDate', 'desc')
            ->setMaxResults(1)
            ->setParameter('currency_code', $code->name)
            ->setParameter('type', $type->name)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
