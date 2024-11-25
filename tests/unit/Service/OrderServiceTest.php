<?php

namespace UnitTests\Service;

use App\Entity\User;
use App\Manager\{OrderManager, UserManager};
use App\Service\OrderBuilderService;
use Doctrine\ORM\{EntityManagerInterface, EntityRepository};
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class OrderServiceTest extends TestCase
{
    /** @var EntityManagerInterface|MockInterface */
    private static $entityManager;

}
