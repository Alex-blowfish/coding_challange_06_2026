<?php

namespace App\DataFixtures;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CartItemFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $cart = $manager->getRepository(Cart::class)->findOneBy([]);

        $chairItem = new CartItem(
            $manager->getRepository(Product::class)->findOneBy(['name' => 'chair']),
            $cart
        );

        $tableItem = new CartItem(
            $manager->getRepository(Product::class)->findOneBy(['name' => 'table']),
            $cart
        );

        $keyboardItem = new CartItem(
            $manager->getRepository(Product::class)->findOneBy(['name' => 'keyboard']),
            $cart
        );

        $manager->persist($chairItem);
        $manager->persist($tableItem);
        $manager->persist($keyboardItem);
        $manager->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [Cart::class, Product::class];
    }
}
