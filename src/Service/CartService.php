<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Console\Exception\InputValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class CartService
{
    public function __construct(
        private CartItemRepository $cartItemRepository,
        private ProductService     $productService,
        private CartRepository     $cartRepository,
        private ValidatorInterface $validator
    )
    {
    }

    /**
     * Get a specific cart, identified by the given cartId.
     *
     * @param int $cartId identifies the cart to find.
     * @throws EntityNotFoundException in case of cart could not be found.
     */
    public function getCart(int $cartId): Cart
    {
        $cart = $this->cartRepository->find($cartId);

        if (!$cart instanceof Cart) {
            throw EntityNotFoundException::fromClassNameAndIdentifier(Cart::class, [(string) $cartId]);
        }

        return $cart;
    }

    /**
     * Get a specific item of a cart, identified by the given cartItemId.
     *
     * @param int $cartItemId identifies the cartItem to find.
     * @throws EntityNotFoundException
     */
    public function getCartItem(int $cartItemId): CartItem
    {
        $cartItemId = $this->cartItemRepository->find($cartItemId);

        if (!$cartItemId instanceof CartItem) {
            throw EntityNotFoundException::fromClassNameAndIdentifier(CartItem::class, [(string) $cartItemId]);
        }

        return $cartItemId;
    }

    /**
     * Depending on existence of a CartItem representing given Product and Cart,
     * a new CartItem will be created or the existing one will be updated.
     * Includes validation of the cartitem which will be created or updated whithin this method.
     *
     * @param int $productId Identifies the product, which will be added to the cart.
     * @param int $cartId Identifies the cart, where the product will be added.
     * @param int $quantity How many products of given prodcut will be added to the cart.
     *
     * @throws EntityNotFoundException
     * @throws InputValidationFailedException
     */
    public function addProduct(int $productId, int $cartId, int $quantity = 1): Cart
    {
        $product = $this->productService->getProduct($productId);
        $cart = $this->getCart($cartId);

        $cartItem = $this->cartItemRepository->findOneBy([
            'product' => $product->getId(),
            'cart' => $cart->getId()
        ]);

        // Create new CartItem which represents a product is added to the cart.
        if (!$cartItem instanceof CartItem) {
            $newCartItem = new CartItem($product, $cart, $quantity);
            $this->validate($newCartItem);

            $this->cartItemRepository->update($newCartItem);
        } else {
            $cartItem->setQuantity($cartItem->getQuantity() + $quantity);
            $this->validate($cartItem);

            $this->cartItemRepository->update($cartItem);
        }

        return $cart;
    }

    /**
     * @throws InputValidationFailedException
     */
    private function validate(object $objectToValidate): void
    {
        $errors = $this->validator->validate($objectToValidate);
        if (0 < $errors->count()) {
            throw new InputValidationFailedException('Input validation failed', $errors);
        }
    }

    /**
     * Removes all products of a specific product-type from a specific cart, no matter the quantity
     * @throws EntityNotFoundException
     */
    public function deleteCartItem(int $cartItemId): Cart
    {
        $cartItem = $this->getCartItem($cartItemId);
        $this->cartItemRepository->delete($cartItem);

        return $cartItem->getCart();
    }

    /**
     * @throws EntityNotFoundException
     * @throws InputValidationFailedException in case of updated CartItem has invalid values.
     */
    public function editCartItem(int $cartItemId, int $quantity): CartItem
    {
        $cartItem = $this->getCartItem($cartItemId);

        $cartItem->setQuantity($quantity);
        $this->validate($cartItem);

        return $this->cartItemRepository->update($cartItem);
    }
}
