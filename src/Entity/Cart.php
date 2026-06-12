<?php

namespace App\Entity;

use App\Repository\CartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\MaxDepth;

/**
 * This class represents a shopping cart. It is able to hold cartItems, which are related to a specific product.
 * This way, the actual product is not directly related to a cart and stays untouched by actions of a customer.
 */
#[ORM\Entity(repositoryClass: CartRepository::class)]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, CartItem>
     */
    #[ORM\OneToMany(targetEntity: CartItem::class, mappedBy: "cart", cascade: ['persist'])]
    #[MaxDepth(2)]
    private Collection $cartItems;

    public function __construct()
    {
        $this->cartItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, CartItem>
     */
    public function getItems(): Collection
    {
        return $this->cartItems;
    }

    public function addItem(CartItem $cartItem): Cart
    {
        if (!$this->cartItems->contains($cartItem)) {
            $this->cartItems->add($cartItem);
        }

        return $this;
    }

    /**
     * Used on serialization
     * @return float Total price of products in this cart.
     */
    public function getTotalPrice(): float
    {
        $totalPrice = 0;
        foreach ($this->getItems() as $item) {
            $totalPrice += ($item->getProduct()->getPrice() * $item->getQuantity());
        }

        return $totalPrice;
    }

    /**
     * Used on serialization
     * @return int Total number of products in this cart.
     */
    public function getTotalNumberOfItems(): int
    {
        $totalNumberOfItems = 0;
        foreach ($this->getItems() as $item) {
            $totalNumberOfItems += $item->getQuantity();
        }

        return $totalNumberOfItems;
    }

    /**
     * Returns true if the given product is added to this cart as a cart item, otherwise false.
     * @param Product $product to search for.
     * @return bool
     */
    public function hasProduct(Product $product): bool
    {
        foreach ($this->getItems() as $item) {
            if ($item->getProduct()->getId() === $product->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retuns the representative cart item of a specific product.
     *
     * @param Product $product Identifies the item to serach for.
     * @return CartItem|null Returns the cart item if found, otherwise null.
     */
    public function getRepresentativeItem(Product $product): ?CartItem
    {
        foreach ($this->getItems() as $item) {
            if ($item->getProduct()->getId() === $product->getId()) {
                return $item;
            }
        }
        return null;
    }
}
