<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Represents a product in a context of a shop.
 * In case of a product will be added to a shopping cart, there will be a relation created
 * which is represented by the class CartItem.
 */
class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $product1 = new Product(
            'table',
            199.99,
            'A big solid wooden table.'
        );
        $manager->persist($product1);

        $product2 = new Product(
            'chair',
            99.99,
            'A classy white chair.'
        );
        $manager->persist($product2);

        $product3 = new Product(
            'carpet',
            149.99,
            'Red carpet. 2m long, 1,05m wide.'
        );
        $manager->persist($product3);

        $product4 = new Product(
            'TV',
            899.99,
            'A big an colorful 70 in TV.'
        );
        $manager->persist($product4);

        $product5 = new Product(
            'keyboard',
            59.99,
            'A Mechanical keyboard.'
        );
        $manager->persist($product5);

        $manager->flush();
    }
}
