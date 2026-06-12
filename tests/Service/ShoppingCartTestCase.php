<?php

namespace App\Tests\Service;

use App\DataFixtures\CartFixtures;
use App\DataFixtures\CartItemFixtures;
use App\DataFixtures\ProductFixtures;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ShoppingCartTestCase extends WebTestCase
{
    final protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        $container = self::getContainer();
        $this->entityManager = $container->get('doctrine')->getManager();

        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $cartFixture = new CartFixtures();
        $cartFixture->load($this->entityManager);

        $cartFixture = new ProductFixtures();
        $cartFixture->load($this->entityManager);

        $cartFixture = new CartItemFixtures();
        $cartFixture->load($this->entityManager);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    /**
     * Checks for expected structure of a cart as a json -> array.
     * @param array<int|string, string|array<string, string>> $cartArray
     * @return void
     */
    protected function assertCartArray(array $cartArray): void
    {
        $this->assertArrayHasKey('id', $cartArray);
        $this->assertArrayHasKey('items', $cartArray);
        $this->assertIsArray($cartArray['items']);
        $this->assertArrayHasKey('totalPrice', $cartArray);
    }

    /**
     * Checks for expected structure of a cartItem as a json -> array.
     * @param array<string, string> $cartItemArray
     * @return void
     */
    protected function assertCartItemArray(array $cartItemArray): void
    {
        $this->assertArrayHasKey('id', $cartItemArray);
        $this->assertArrayHasKey('product', $cartItemArray);
        $this->assertArrayHasKey('quantity', $cartItemArray);
    }

    /**
     * Checks for expected structure of a product as a json -> array.
     * @param array<string, string> $productArray
     * @return void
     */
    protected function assertProductArray(array $productArray): void
    {
        $this->assertArrayHasKey('id', $productArray);
        $this->assertArrayHasKey('name', $productArray);
        $this->assertArrayHasKey('price', $productArray);
        $this->assertArrayHasKey('description', $productArray);
    }
}
