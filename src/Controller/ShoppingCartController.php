<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\ShoppingCart;
use App\Repository\ProductRepository;
use App\Repository\ShoppingCartRepository;
use App\Service\ProductService;
use App\Service\ShoppingCartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class ShoppingCartController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProductRepository      $productRepository,
        private readonly ShoppingCartRepository $shoppingCartRepository,
        private readonly ProductService         $productService,
        private readonly ShoppingCartService    $shoppingCartService,
    ) {
    }

    #[Route('/cart', name: 'api_cart_get', methods: ['GET'])]
    public function get(Request $request): JsonResponse
    {
        try {
            $shoppingCart = $this->shoppingCartService->getShoppingCart();
        } catch (\Throwable $th) {
            return $this->json([
                'error' => $th->getMessage(),
            ], $th->getCode() ?: JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'cart' => [
                'uuid'     => $shoppingCart->getUuid(),
                'products' => $shoppingCart->getProducts(),
            ],
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/cart', name: 'api_cart_add', methods: ['POST'])]
    public function add(Request $request): JsonResponse
    {
        try {
            $payload = json_decode($request->getContent(), true) ?? null;
            $quantity = $payload['quantity'] ?? 1;

            if ($quantity <= 0) {
                throw new \Exception('Invalid quantity', JsonResponse::HTTP_BAD_REQUEST);
            }

            $product      = $this->productService->getProduct();
            $shoppingCart = $this->shoppingCartService->getShoppingCart($payload);

            $shoppingCart->addProduct($product, $quantity);

            $this->entityManager->persist($shoppingCart);
            $this->entityManager->flush();
        } catch (\Throwable $th) {
            return new JsonResponse([
                'error' => $th->getMessage(),
            ], $th->getCode() ?: JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'message' => 'Successfully added product(s) to cart',
            'cart'    => [
                'uuid'     => $shoppingCart->getUuid(),
                'products' => $shoppingCart->getProducts(),
            ],
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/cart', name: 'api_cart_update', methods: ['PATCH'])]
    public function update(Request $request): JsonResponse
    {
        try {
            $payload = json_decode($request->getContent(), true) ?? null;
            $newQuantity  = $payload['quantity'] ?? null;

            if (!$newQuantity) {
                throw new \Exception('No quantity found in payload', JsonResponse::HTTP_BAD_REQUEST);
            }
            if ($newQuantity <= 0) {
                throw new \Exception('Invalid quantity', JsonResponse::HTTP_BAD_REQUEST);
            }
            
            $shoppingCart = $this->shoppingCartService->getShoppingCart();
            $product      = $this->productService->getProduct();

            $shoppingCart->updateProduct($product);

            $this->entityManager->persist($shoppingCart);
            $this->entityManager->flush();
        } catch (\Throwable $th) {
            return $this->json([
                'error' => $th->getMessage(),
            ], $th->getCode() ?: JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        $verb        = $newQuantity > 0 ? 'updated' : 'deleted';
        $preposition = $newQuantity > 0 ? 'in' : 'from';

        return $this->json([
            'message' => 'Successfully '. $verb . ' product(s) ' . $preposition . ' cart',
            'cart'    => [
                'uuid'     => $shoppingCart->getUuid(),
                'products' => $shoppingCart->getProducts(),
            ],
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/cart', name: 'api_cart_delete', methods: ['DELETE'])]
    public function delete(Request $request): JsonResponse 
    {
        try {
            $shoppingCart = $this->shoppingCartService->getShoppingCart();
            $product      = $this->productService->getProduct();

            $shoppingCart->removeProduct($product);

            $this->entityManager->persist($shoppingCart);
            $this->entityManager->flush();
        } catch (\Throwable $th) {
            return $this->json([
                'error' => $th->getMessage(),
            ], $th->getCode() ?: JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'message' => 'Successfully deleted product(s) from cart',
            'cart'    => [
                'uuid'     => $shoppingCart->getUuid(),
                'products' => $shoppingCart->getProducts(),
            ],
        ], JsonResponse::HTTP_OK);
    }
}