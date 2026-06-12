<?php

namespace App\Tests\Service;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Service\CartService;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Console\Exception\InputValidationFailedException;

class CartServiceTest extends ShoppingCartTestCase
{
    private CartService $systemUnderTest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->systemUnderTest = self::getContainer()->get(CartService::class);
    }

    public function testGetCart(): void
    {
        /** @var Cart $cart */
        $cart = $this->entityManager->getRepository(Cart::class)->findOneBy([]);

        self::assertSame(
            $this->systemUnderTest->getCart($cart->getId()),
            $cart
        );
    }

    public function testEntityNotFoundExceptionOnGetCart(): void
    {
        $this->expectException(EntityNotFoundException::class);
        $this->systemUnderTest->getCart(-4567);
    }

    /**
     * @return void
     */
    public function testGetCartItem(): void
    {
        /** @var CartItem $cartItem */
        $cartItem = $this->entityManager->getRepository(CartItem::class)->findOneBy([]);

        self::assertSame(
            $this->systemUnderTest->getCartItem($cartItem->getId()),
            $cartItem
        );
    }

    public function testEntityNotFoundExceptionOnGetCartItem(): void
    {
        $this->expectException(EntityNotFoundException::class);
        $this->systemUnderTest->getCartItem(-7638945);
    }

    /**
     * This Test covers the deletion of the cart item itself as well as the correct cascading of this deletion.
     * @return void
     */
    public function testDeleteCartItem(): void
    {
        /** @var CartItem $cartItem */
        $cartItem = $this->entityManager->getRepository(CartItem::class)->findOneBy([]);
        $cartItemId = $cartItem->getId();
        $relatedCart = $cartItem->getCart();
        $relatedCartId = $cartItem->getCart()->getId();
        $relatedProductId = $cartItem->getProduct()->getId();

        $this->systemUnderTest->deleteCartItem($cartItem->getId());

        self::assertNull(
            $this->entityManager->getRepository(CartItem::class)->find($cartItemId),
            'CartItem should must existing.'
        );

        self::assertNotContains(
            $cartItem,
            $relatedCart->getItems(),
            'CartItem should must be available in the cart.'
        );

        self::assertInstanceOf(
            Product::class,
            $this->entityManager->getRepository(Product::class)->find($relatedProductId),
            'Related product must not been deleted.'
        );

        self::assertInstanceOf(
            Cart::class,
            $this->entityManager->getRepository(Cart::class)->find($relatedCartId),
            'Related product must not been deleted.'
        );
    }

    public function testEntityNotFoundExceptionOnDeleteCardItem(): void
    {
        $this->expectException(EntityNotFoundException::class);
        $this->systemUnderTest->deleteCartItem(-7638945);
    }

    public function testEntityNotFoundExceptionOnUpdateCardItem(): void
    {
        $this->expectException(EntityNotFoundException::class);
        $this->systemUnderTest->editCartItem(-4433, 8);
    }

    public function testInputValidationFailedExceptionOnUpdateCardItem(): void
    {
        /** @var CartItem $cartItem */
        $cartItem = $this->entityManager->getRepository(CartItem::class)->findOneBy([]);

        $this->expectException(InputValidationFailedException::class);
        $this->systemUnderTest->editCartItem($cartItem->getId(), -5678);
    }

    /**
     * Tests an update of an existing cart item by setting the quantity.
     */
    public function testUpdateCartItem(): void
    {
        /** @var CartItem $cartItem */
        $cartItem = $this->entityManager->getRepository(CartItem::class)->findOneBy([]);

        $updatedItem = $this->systemUnderTest->editCartItem($cartItem->getId(), 99);
        self::assertSame(
            99,
            $updatedItem->getQuantity(),
            'Updated quantity should be equal to 99.'
        );
    }

    /**
     * Tests adding a product to a cart.
     * A representative cart item should be created and attached to the cart.
     * The quantity needs to be set correctly.
     */
    public function testAddProductToCart(): void
    {
        /** @var Product $tvProduct */
        $tvProduct = $this->entityManager->getRepository(Product::class)->findOneBy(['name' => 'TV']);

        /** @var Cart $randomCart */
        $randomCart = $this->entityManager->getRepository(Cart::class)->findOneBy([]);

        $quantityBefore = $randomCart->getTotalNumberOfItems();

        $updatedCart = $this->systemUnderTest->addProduct($tvProduct->getId(), $randomCart->getId());
        self::assertEquals($quantityBefore + 1, $updatedCart->getTotalNumberOfItems());
        self::assertTrue($randomCart->hasProduct($tvProduct));
    }

    /**
     * Tests if the quantity is correctly updated in case of a product is added to a cart.
     */
    public function testIncreaseQuantityOnAddProductToCart(): void
    {
        /** @var Product $tableProduct */
        $tableProduct = $this->entityManager->getRepository(Product::class)->findOneBy(['name' => 'table']);

        /** @var Cart $randomCart */
        $randomCart = $this->entityManager->getRepository(Cart::class)->findOneBy([]);

        //check setup:
        self::assertTrue($randomCart->hasProduct($tableProduct));

        $amountOfTablesInTheCartBefore = $randomCart->getRepresentativeItem($tableProduct)->getQuantity();
        $quantityBefore = $randomCart->getTotalNumberOfItems();

        // actual test
        $updatedCart = $this->systemUnderTest->addProduct($tableProduct->getId(), $randomCart->getId());

        self::assertEquals($quantityBefore+1, $updatedCart->getTotalNumberOfItems());
        self::assertTrue($randomCart->hasProduct($tableProduct));
        self::assertEquals(
            $amountOfTablesInTheCartBefore + 1,
            $randomCart->getRepresentativeItem($tableProduct)->getQuantity()
        );
    }
}
