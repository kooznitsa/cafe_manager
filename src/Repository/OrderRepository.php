<?php

namespace App\Repository;

use App\Enum\Status;
use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct($registry, Order::class);
    }

    public function getPaidOrders(int $page, int $perPage): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('date(o.createdAt) AS orderDate, SUM(d.price) AS total')
            ->from(Order::class, 'o')
            ->leftJoin('o.dish', 'd')
            ->where('o.status IN (:statusList)')
            ->setParameter('statusList', [Status::Paid->name, Status::Delivered->name])
            ->groupBy('orderDate')
            ->orderBy('orderDate', 'DESC')
            ->setFirstResult($perPage * $page)
            ->setMaxResults($perPage);

        return $qb->getQuery()->getResult();
    }
}
