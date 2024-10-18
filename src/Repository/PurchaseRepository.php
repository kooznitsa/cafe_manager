<?php

namespace App\Repository;

use App\Entity\Purchase;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Purchase>
 */
class PurchaseRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct($registry, Purchase::class);
    }

    /**
     * @return Purchase[]
     */
    public function getPurchases(
        int $page,
        int $perPage,
        ?DateTime $dateFrom,
        ?DateTime $dateTo,
        ?int $productId,
    ): array {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('p')
            ->from(Purchase::class, 'p');
        if ($dateFrom) {
            $qb->where('p.createdAt >= :dateFrom')
                ->setParameter('dateFrom', $dateFrom);
        }
        if ($dateTo) {
            $qb->andWhere('p.createdAt <= :dateTo')
                ->setParameter('dateTo', $dateTo);
        }
        if ($productId) {
            $qb->andWhere('p.product = :productId')
                ->setParameter('productId', $productId);
        }

        $qb->orderBy('p.id', 'DESC')
            ->setFirstResult($perPage * $page)
            ->setMaxResults($perPage);

        return $qb->getQuery()->getResult();
    }
}
