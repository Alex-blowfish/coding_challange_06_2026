<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Represents a product, which can be added to a shopping cart.
 */
#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[Groups(['list', 'detail'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['list', 'detail'])]
    #[ORM\Column(length: 255, unique: true, nullable: false)]
    private string $name;

    #[Groups(['list', 'detail'])]
    #[Assert\Positive]
    #[ORM\Column(type: Types::FLOAT, nullable: false, options: ['unsigned' => true])]
    private float $price;

    #[Groups(['detail'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    public function __construct(string $name, float $price, ?string $description = null)
    {
        $this->name = $name;
        $this->price = $price;
        $this->description = $description;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $Price): static
    {
        $this->price = $Price;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }
}
