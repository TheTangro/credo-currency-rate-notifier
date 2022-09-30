<?php

namespace App\Repository;

use App\Entity\Proxy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Proxy>
 *
 * @method Proxy|null find($id, $lockMode = null, $lockVersion = null)
 * @method Proxy|null findOneBy(array $criteria, array $orderBy = null)
 * @method Proxy[]    findAll()
 * @method Proxy[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProxyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Proxy::class);
    }

    public function save(Proxy $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Proxy $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getLeastUsed(): ?Proxy
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.stats', 'ps')
            ->orderBy('ps.usageCounter', 'asc')
            ->orderBy('ps.errorsCounter', 'asc')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function truncate(): void
    {
        $cmd = $this->getEntityManager()->getClassMetadata($this->getClassName());
        $connection = $this->getEntityManager()->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $query = $dbPlatform->getTruncateTableSql($cmd->getTableName());

        $connection->executeStatement($query);
    }

    /**
     * @param iterable<Proxy> $entityGenerator
     *
     * @return int
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function refillTable(iterable $entityGenerator): int
    {
        $connection = $this->getEntityManager()->getConnection();
        $connection->beginTransaction();

        try {
            $counter = 0;
            $this->createQueryBuilder('e')
                ->delete()
                ->getQuery()
                ->execute();

            foreach ($entityGenerator as $proxy) {
                $counter++;
                $this->save($proxy);
            }

            if ($counter === 0) {
                throw new \InvalidArgumentException('Zero proxies parsed');
            }

            $this->getEntityManager()->flush();
        } catch (\Exception $e) {
            $connection->rollback();
        }

        $connection->commit();

        return $counter;
    }
}
