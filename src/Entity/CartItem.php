<?php

namespace App\Entity;

use App\Repository\CartItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * This represents a relation between a product and a cart.
 * It allows to store the amount as well as the details of a specific product which is added to a specific cart.
 * The Instances can be deleted, in case of the related product is no longer added to a cart.
 */
#[ORM\Entity(repositoryClass: CartItemRepository::class)]
class CartItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[ORM\OneToOne(targetEntity: Product::class)]
    private Product $product;

    #[Assert\NotBlank]
    #[ORM\ManyToOne(targetEntity: Cart::class, inversedBy: "cartItems", cascade: ['persist'])]
    #[Ignore]
    private Cart $cart;

    #[Assert\NotBlank]
    #[Assert\Positive]
    #[ORM\Column(nullable: false, options: ['unsigned' => true, 'default' => 1])]
    private int $quantity = 1;

    public function __construct(Product $product, Cart $cart, int $quantity = 1)
    {
        $this->product = $product;
        $this->cart = $cart;
        $this->cart->addItem($this);
        $this->quantity = $quantity;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function setQuantity(int $quantity): CartItem
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

}
