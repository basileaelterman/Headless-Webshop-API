<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\ShoppingCart;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ShoppingCartController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProductRepository      $productRepository,
    ) {
    }

    private function createShoppingCart(User $user): ShoppingCart
    {
        $shoppingCart = new ShoppingCart();

        $user->setShoppingCart($shoppingCart);

        $this->entityManager->persist($shoppingCart);
        $this->entityManager->flush();

        return $shoppingCart;
    }

    #[Route('/cart', name: 'api_shoppingcart', methods: ['POST'])]
    public function add(Request $request): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user) {
                throw new \Exception('Unauthorized', JsonResponse::HTTP_UNAUTHORIZED);
            }

            $payload = json_decode($request->getContent(), true) ?? null;

            if (!$payload) {
                throw new \Exception('Invalid payload', JsonResponse::HTTP_BAD_REQUEST);
            }

            $product  = $payload['product'] ?? null;
            $quantity = $payload['quantity'] ?? 1;
            $slug     = $product['slug'] ?? $payload['slug'] ?? null;
            
            if (!$product && !$slug) {
                throw new \Exception('No product found in payload', JsonResponse::HTTP_BAD_REQUEST);
            }
            if ($quantity <= 0) {
                throw new \Exception('Invalid quantity', JsonResponse::HTTP_BAD_REQUEST);
            }
            if (!$slug) {
                throw new \Exception('No slug found in payload', JsonResponse::HTTP_BAD_REQUEST);
            }

            // Verify wether product actually exists
            $productExists = $this->productRepository->findOneBy(['slug' => $slug]);

            if (!$productExists) {
                throw new \Exception('Product does not exist', JsonResponse::HTTP_BAD_REQUEST);
            }

            // Add product to cart
            $shoppingCart = $user->getShoppingCart();

            if (!$shoppingCart) {
                $shoppingCart = $this->createShoppingCart($user);
            }

            $shoppingCart->addProduct($productExists, $quantity);

            $this->entityManager->persist($shoppingCart);
            $this->entityManager->flush();
        } catch (\Throwable $th) {
            return new JsonResponse([
                'error' => $th->getMessage(),
            ], $th->getCode() ?: JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'message' => 'Successfully added product to cart',
            'cart' => [
                'id'       => $shoppingCart->getId(),
                'products' => $shoppingCart->getProducts(),
            ],
        ], JsonResponse::HTTP_OK);
    }

/*
    #[Route('/cart', name: 'api_cart', methods: ['PATCH'])]
    public function update(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            throw new \Exception('Unauthorized', JsonResponse::HTTP_UNAUTHORIZED);
        }

        $payload = json_decode($request->getContent(), true) ?? null;

        if (!$payload) {
            throw new \Exception('Invalid payload', JsonResponse::HTTP_BAD_REQUEST);
        }
    }
*/
}