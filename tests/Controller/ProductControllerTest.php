<?php

namespace App\Tests\Controller;

use App\Tests\Service\ShoppingCartTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductControllerTest extends ShoppingCartTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    public function testGetProductsRequest(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/v1/products');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseContent = $this->client->getResponse()->getContent();
        $this->assertJson($responseContent);
        $responseData = json_decode($responseContent, true);

        $this->assertCount(5, $responseData);
        foreach ($responseData as $productArray) {
            $this->assertArrayHasKey('id', $productArray);
            $this->assertArrayHasKey('name', $productArray);
            $this->assertArrayHasKey('price', $productArray);
        }
    }

    public function testGetProductRequest(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/v1/products/1');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseContent = $this->client->getResponse()->getContent();
        $this->assertJson($responseContent);
        $responseData = json_decode($responseContent, true);

        $this->assertCount(4, $responseData);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('price', $responseData);
        $this->assertArrayHasKey('description', $responseData);
    }

    public function testGetProductRequestNotFound(): void
    {
        $this->client->request('GET', '/api/v1/products/-1');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertJson($responseContent);
    }
}
