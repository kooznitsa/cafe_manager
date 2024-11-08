<?php

namespace App\Manager;

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

    public function saveProduct(string $name, string $unit, float $amount = 0): ?int
    {
        $product = new Product();
        $product->setName($name)->setUnit($unit)->setAmount($amount);
        $this->save($product);

        return $product->getId();
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
        ?string $name = null,
        ?string $unit = null,
        ?float $amount = null,
        bool $isFlush = true,
    ): ?Product {
        if (!$product) {
            return null;
        }
        if ($name !== null) {
            $product->setName($name);
        }
        if ($unit !== null) {
            $product->setUnit($unit);
        }
        if ($amount !== null) {
            $product->setAmount($amount);
        }

        if ($isFlush) {
            $this->entityManager->flush();
        }

        return $product;
    }

    public function deleteProduct(Product $product): bool
    {
        $this->entityManager->remove($product);
        $this->entityManager->flush();

        return true;
    }

    public function deleteProductById(int $productId): bool
    {
        /** @var Product $product */
        $product = $this->productRepository->find($productId);
        if (!$product) {
            return false;
        }
        return $this->deleteProduct($product);
    }
}
