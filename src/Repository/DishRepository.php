<?php

namespace App\Repository;

use App\Entity\{Category, Dish};
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Dish>
 */
class DishRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dish::class);
    }

        /**
         * @return Dish[] Returns an array of Dish objects
         */
        public function findByCategory(Category $category): array
        {
            return $this->createQueryBuilder('d')
                ->andWhere('d.category = :category')
                ->setParameter('category', $category)
                ->orderBy('d.name', 'ASC')
                ->getQuery()
                ->getResult()
            ;
        }
}
