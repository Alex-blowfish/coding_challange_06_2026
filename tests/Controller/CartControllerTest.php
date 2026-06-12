<?php

namespace App\Tests\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Tests\Service\ShoppingCartTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CartControllerTest extends ShoppingCartTestCase
{
    private KernelBrowser $client;
    protected function setUp(): void
    {
        $this->client = static::createClient();
        parent::setUp();
    }

    /**
     * Tests if there is a valid json response even if the cart is empty.
     */
    public function testEmptyCart(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/v1/carts/1');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseContent = $this->client->getResponse()->getContent();
        $this->assertJson($responseContent);
        $responseData = json_decode($responseContent, true);

        $this->assertCartArray($responseData);
    }

    /**
     * Test returned json structure of a filled cart.
     */
    public function testGetFilledCart(): void
    {
        $count = $this->entityManager->getRepository(CartItem::class)->count();

        $this->client->request(Request::METHOD_GET, '/api/v1/carts/1');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseContent = $this->client->getResponse()->getContent();
        $this->assertJson($responseContent);
        $responseData = json_decode($responseContent, true);

        $this->assertCartArray($responseData);
        $this->assertCount($count, $responseData['items']);
        $this->assertCartItemArray($responseData['items'][0]);
        $this->assertProductArray($responseData['items'][0]['product']);
    }

    public function testGetCartRequestNotFound(): void
    {
        $this->client->request('GET', '/api/v1/carts/-1');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertJson($responseContent);
    }

    public function testRemoveItemFromCartNotFound(): void
    {
        $this->client->request(Request::METHOD_DELETE, '/api/v1/carts/1/items/-1');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertJson($responseContent);
    }

    public function testRemoveItemFromCart(): void
    {
        $randomCartItem = $this->entityManager->getRepository(CartItem::class)->findOneBy([]);

        $this->client->request(
            Request::METHOD_DELETE,
            '/api/v1/carts/1/items/'.$randomCartItem->getId()
        );

        $this->assertResponseIsSuccessful();
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertJson($responseContent);
        $responseData = json_decode($responseContent, true);

        $this->assertCartArray($responseData);
        $this->assertCount(2, $responseData['items']);
    }

    /**
     * Testing the request to create&add a new cart item to a existing cart.
     */
    public function testAddRequest(): void
    {
        $tvProduct = $this->entityManager->getRepository(Product::class)->findOneBy(['name' => 'TV']);
        $randomCart = $this->entityManager->getRepository(Cart::class)->findOneBy([]);
        $itemsInCartBefore = $randomCart->getItems()->count();

        $this->client->request(
            Request::METHOD_POST,
            '/api/v1/carts/'.$randomCart->getId().'/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product_id' => $tvProduct->getId(),
                'cart_id' => 1
            ])
        );

        $this->assertResponseIsSuccessful();
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertJson($responseContent);
        $responseData = json_decode($responseContent, true);

        $this->assertCartArray($responseData);
        $this->assertCount($itemsInCartBefore + 1, $responseData['items']);
        $this->assertCartItemArray($responseData['items'][0]);
        $this->assertProductArray($responseData['items'][0]['product']);

        $this->assertEquals($tvProduct->getId(), $responseData['items'][$itemsInCartBefore]['product']['id']);
    }

    /**
     * Test an update of a cart-item by setting new value of quantity.
     */
    public function testPatchRequest(): void
    {
        $randomCartItem = $this->entityManager->getRepository(CartItem::class)->findOneBy([]);
        $quantity = 99;

        $this->client->request(
            Request::METHOD_PATCH,
            '/api/v1/carts/1/items/'.$randomCartItem->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['quantity' => $quantity])
        );

        $this->assertResponseIsSuccessful();
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertJson($responseContent);
        $responseData = json_decode($responseContent, true);

        $this->assertCartArray($responseData);
        $this->assertCount(3, $responseData['items']);
        $this->assertCartItemArray($responseData['items'][0]);
        $this->assertProductArray($responseData['items'][0]['product']);

        $this->assertEquals($quantity, $responseData['items'][$randomCartItem->getId() - 1]['quantity']);
    }
}
