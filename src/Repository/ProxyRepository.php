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
            ->innerJoin('p.proxyStats', 'ps')
            ->orderBy('ps.usageCounter', 'asc')
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
}
