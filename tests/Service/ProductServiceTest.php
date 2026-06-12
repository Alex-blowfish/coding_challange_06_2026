<?php

namespace App\Tests\Service;

use App\Entity\Product;
use App\Service\ProductService;
use Doctrine\ORM\EntityNotFoundException;

class ProductServiceTest extends ShoppingCartTestCase
{
    private ProductService $systemUnderTest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->systemUnderTest = self::getContainer()->get(ProductService::class);
    }

    public function testGetProducts(): void
    {
        /** @var array<int, Product> $products */
        $products = $this->entityManager->getRepository(Product::class)->findAll();

        self::assertSame(
            $this->systemUnderTest->getProducts(),
            $products
        );
    }

    public function testGetProduct():void
    {
        /** @var Product $randomProducts */
        $randomProducts = $this->entityManager->getRepository(Product::class)->findOneBy([]);

        self::assertSame(
            $this->systemUnderTest->getProduct($randomProducts->getId()),
            $randomProducts
        );
    }

    public function testEntityNotFoundExceptionOnGetProduct(): void
    {
        $this->expectException(EntityNotFoundException::class);
        $this->systemUnderTest->getProduct(-4567);
    }
}
