<?php

namespace App\Controller;

use App\Service\ProductService;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductService $productService
    )
    {
    }

    #[Route(path: '/api/v1/products', name: 'list_products', methods: ['GET'])]
    public function listProducts(): JsonResponse
    {
        return $this->json(
            $this->productService->getProducts(),
            Response::HTTP_OK,
            ['Cache-Control' => 'public, max-age=3600'],
            [
                'groups' => ['list'],
                'json_encode_options' => JSON_PRETTY_PRINT
            ]
        );
    }

    #[Route(path: '/api/v1/products/{productId}', name: 'get_product', methods: ['GET'])]
    public function getProduct(int $productId): JsonResponse
    {
        try {
            $product = $this->productService->getProduct($productId);
        } catch (EntityNotFoundException $e) {
            return $this->json(null, Response::HTTP_NOT_FOUND);
        }

        return $this->json(
            $product,
            Response::HTTP_OK,
            ['Cache-Control' => 'public, max-age=3600'],
            [
                'groups' => ['detail'],
                'json_encode_options' => JSON_PRETTY_PRINT
            ]
        );
    }
}
