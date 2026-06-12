<?php

namespace App\Repository;

use App\Entity\CartItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CartItem>
 */
class CartItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CartItem::class);
    }

    /**
     * Deletes the given cartItem form the database, which means the product will be removed from the cart.
     *
     * @param CartItem $cartItem Object which will be deleted from the database.
     */
    public function delete(CartItem $cartItem): void
    {
        $this->getEntityManager()->remove($cartItem);
        $this->getEntityManager()->flush();
    }

    /**
     * Updates the database entry of given Object.
     * Returns updated object.
     *
     * @param CartItem $cartItem Object which data will be stored in the database.
     * @return CartItem Updated object.
     */
    public function update(CartItem $cartItem): CartItem
    {
        $this->getEntityManager()->persist($cartItem);
        $this->getEntityManager()->flush();

        return $cartItem;
    }
}
