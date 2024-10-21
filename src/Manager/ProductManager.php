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

    public function saveProduct(string $name, string $unit): ?int
    {
        $product = new Product();
        $product->setName($name)->setUnit($unit);

        $this->entityManager->persist($product);
        $this->entityManager->flush();

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

    public function updateProduct(int $productId, ?string $name = null, ?string $unit = null): ?Product
    {
        /** @var Product $product */
        $product = $this->productRepository->find($productId);
        if (!$product) {
            return null;
        }
        if ($name) {
            $product->setName($name);
        }
        if ($unit) {
            $product->setUnit($unit);
        }
        $this->entityManager->flush();

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
        /** @var Product $dish */
        $product = $this->productRepository->find($productId);
        if (!$product) {
            return false;
        }
        return $this->deleteProduct($product);
    }
}
