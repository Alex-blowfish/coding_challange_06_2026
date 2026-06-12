<?php

namespace App\Controller;

use App\Service\CartService;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Exception\InputValidationFailedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    public function __construct(private readonly CartService $cartItemService)
    {
    }

    /**
     * Get a specific cart, which will be displayed as a list of items(products),
     * which are currently added to the cart.
     */
    #[Route(path: '/api/v1/carts/{cartId}', name: 'get_cart', methods: ['GET'])]
    public function getCart(int $cartId, CartService $cartItemService): JsonResponse
    {
        try {
            $cart = $cartItemService->getCart($cartId);
        } catch (EntityNotFoundException $entityNotFoundException) {
            return $this->generateNotFoundResponse($entityNotFoundException);
        }

        return $this->json(
            $cart,
            Response::HTTP_OK,
            ['Cache-Control' => 'public, max-age=3600'],
            [
                'json_encode_options' => JSON_PRETTY_PRINT
            ]
        );
    }

    #[Route(path: '/api/v1/carts/{cartId}/items', name: 'cart_add_item', methods: ['POST'])]
    public function addCartItem(int $cartId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $productId = $data['product_id'] ?? null;
        $quantity = $data['quantity'] ?? 1;

        try {
            $cart = $this->cartItemService->addProduct($productId, $cartId, $quantity);
        } catch (EntityNotFoundException $entityNotFoundException) {
            return $this->generateNotFoundResponse($entityNotFoundException);
        } catch (InputValidationFailedException $inputValidationFailedException) {
            return $this->generateErrorResponse($inputValidationFailedException);
        }

        return $this->json(
            $cart,
            Response::HTTP_OK,
            ['Cache-Control' => 'public, max-age=3600'],
            ['json_encode_options' => JSON_PRETTY_PRINT]
        );
    }

    #[Route(path: '/api/v1/carts/{cartId}/items/{cartItemId}', name: 'cart_update_item', methods: ['PATCH'])]
    public function updateCartItem(int $cartItemId, Request $request): JsonResponse|RedirectResponse
    {
        $data = json_decode($request->getContent(), true);
        $quantity = $data['quantity'];

        try {
            $cartItem = $this->cartItemService->getCartItem($cartItemId);
        } catch (EntityNotFoundException $entityNotFoundException) {
            return $this->generateNotFoundResponse($entityNotFoundException);
        }

        $responseCode = $quantity !== $cartItem->getQuantity() ? Response::HTTP_OK : Response::HTTP_NOT_MODIFIED;

        try {
            $cartItem = $this->cartItemService->editCartItem($cartItem->getId(), $quantity);
        } catch (InputValidationFailedException $inputValidationFailedException) {
            return $this->generateErrorResponse($inputValidationFailedException);
        }

        return $this->json(
            $cartItem->getCart(),
            $responseCode,
            ['Cache-Control' => 'public, max-age=3600'],
            ['json_encode_options' => JSON_PRETTY_PRINT]
        );
    }

    #[Route(path: '/api/v1/carts/{cartId}/items/{cartItemId}', name: 'cart_remove_item', methods: ['DELETE'])]
    public function removeCartItem(int $cartItemId): JsonResponse
    {
        try {
            $cart = $this->cartItemService->deleteCartItem($cartItemId);
        } catch (EntityNotFoundException $entityNotFoundException) {
            return $this->generateNotFoundResponse($entityNotFoundException);

        }

        return $this->json(
            $cart,
            Response::HTTP_OK,
            ['Cache-Control' => 'public, max-age=3600'],
            ['json_encode_options' => JSON_PRETTY_PRINT]
        );
    }

    private function generateErrorResponse(
        InputValidationFailedException $inputValidationFailedException
    ): JsonResponse
    {
        $details = [];
        foreach ($inputValidationFailedException->getViolations() as $violation) {
            $details['field'] = $violation->getPropertyPath();
            $details['message'] = $violation->getMessage();
        }

        $errorResponse = [
            'error' => [
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => 'Validation failed',
                'description' => 'The request contains invalid data.',
                'details' => $details,
            ]
        ];

        return $this->json(
            $errorResponse,
            Response::HTTP_BAD_REQUEST,
            ['Cache-Control' => 'public, max-age=3600'],
            ['json_encode_options' => JSON_PRETTY_PRINT]
        );
    }

    private function generateNotFoundResponse(EntityNotFoundException $entityNotFoundException): JsonResponse
    {
        $errorResponse = [
            'error' => [
                'code' => Response::HTTP_NOT_FOUND,
                'message' => 'Not found',
                'description' => $entityNotFoundException->getMessage(),
                'details' => [
                    "resource" => $entityNotFoundException->getTraceAsString(),
                ],
            ]
        ];

        return $this->json(
            $errorResponse,
            Response::HTTP_NOT_FOUND,
            ['Cache-Control' => 'public, max-age=3600'],
            ['json_encode_options' => JSON_PRETTY_PRINT]
        );

    }
}
