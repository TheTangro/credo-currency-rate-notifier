<?php

namespace App\Repository;

use App\Entity\MessageHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

/**
 * @extends ServiceEntityRepository<MessageHistory>
 *
 * @method MessageHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method MessageHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method MessageHistory[]    findAll()
 * @method MessageHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageHistoryRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly ContainerBagInterface $containerBag
    ) {
        parent::__construct($registry, MessageHistory::class);
    }

    public function save(MessageHistory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MessageHistory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getBySender(string $sender): ?MessageHistory
    {
        $dateInterval = $this->containerBag->get('app.notification.min_gap');

        return $this->createQueryBuilder('m')
            ->andWhere('m.sender = :val')
            ->andWhere('m.sentDate BETWEEN :from and :to')
            ->setParameter('val', $sender)
            ->setParameter('from', (new \DateTimeImmutable())->modify("-{$dateInterval} seconds"))
            ->setParameter('to', new \DateTimeImmutable())
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
