<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityNotFoundException;

readonly class ProductService
{
    public function __construct(private ProductRepository $productRepository)
    {
    }

    /**
     * @return array<int, Product>
     */
    public function getProducts(): array
    {
        return $this->productRepository->findAll();
    }

    /**
     * Get a specific product, identified by the given productId.
     *
     * @param int $productId identifies the product to find.
     * @throws EntityNotFoundException in product of cart could not be found.
     */
    public function getProduct(int $productId): Product
    {
        $product = $this->productRepository->find($productId);

        if (!$product instanceof Product) {
            throw EntityNotFoundException::fromClassNameAndIdentifier(Product::class, [(string) $productId]);
        }

        return $product;
    }
}
