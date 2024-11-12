<?php

namespace App\Manager;

use App\DTO\Request\ProductRequestDTO;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProductManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProductRepository $productRepository,
    ) {
    }

    public function save(Product $product): void
    {
        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }

    public function saveProduct(ProductRequestDTO $dto): Product
    {
        $product = new Product();
        $product->setName($dto->name)->setUnit($dto->unit)->setAmount($dto->amount);
        $this->save($product);

        return $product;
    }

    /**
     * @return Product[]
     */
    public function getProducts(): array
    {
        return $this->productRepository->findAll();
    }

    public function getProductById(int $id): ?Product
    {
        return $this->productRepository->find($id);
    }

    public function updateProduct(
        ?Product $product,
        ProductRequestDTO $dto,
        bool $isFlush = true,
    ): ?Product {
        if (!$product) {
            return null;
        }
        if ($dto->name !== null) {
            $product->setName($dto->name);
        }
        if ($dto->unit !== null) {
            $product->setUnit($dto->unit);
        }
        if ($dto->amount !== null) {
            $product->setAmount($dto->amount);
        }

        if ($isFlush) {
            $this->entityManager->flush();
        }

        return $product;
    }

    public function deleteProduct(?Product $product): bool
    {
        if (!$product) {
            return false;
        }

        $this->entityManager->remove($product);
        $this->entityManager->flush();

        return true;
    }
}
