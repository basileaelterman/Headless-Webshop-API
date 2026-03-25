<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    private $MIN_QUANTITY = 1;
    private $MAX_QUANTITY = 50;

    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly ProductService    $productService,
    ) {
    }

    #[Route('/products', name: 'api_products', methods: ['GET'])]
    public function getProducts(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!$payload) {
            return $this->json([
                'error' => 'Invalid payload',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $quantity     = (int) $payload['quantity'];
        $encodedToken = $payload['token'] ?? null; // The clientside sends a Base64 string of the ID of the last product they fetched

        if ($quantity < $this->MIN_QUANTITY || $quantity > $this->MAX_QUANTITY) {
            $error = $quantity < $this->MIN_QUANTITY ? 'Smallest allowed quantity is ' . $this->MIN_QUANTITY
                                                     : 'Largest allowed quantity is ' . $this->MAX_QUANTITY;

            return $this->json([
                'error' => $error,
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $lastId = null;

        if ($encodedToken) {
            $lastId = base64_decode($encodedToken, true);

            if ($lastId === false || !is_numeric($lastId)) {
                return $this->json([
                    'error' => 'Invalid token',
                ], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        $criteria = $lastId ? ['id' => ['<', $lastId]] : [];

        try {
            $queryBuilder = $this->productRepository->createQueryBuilder('product')
                                                    ->orderBy('product.id', 'DESC')
                                                    ->setMaxResults($quantity + 1);

            if ($lastId) {
                $queryBuilder->andWhere('product.id < :lastId')
                             ->setParameter('lastId', $lastId);
            }

            $products = $queryBuilder->getQuery()->getResult();

            $hasMore = count($products) > $quantity;

            if ($hasMore) {
                array_pop($products);
            }

            $lastProduct = end($products);
            $token = ($hasMore && $lastProduct) ? base64_encode((string) $lastProduct->getId()) // We send a token back to the clientside and expect
                                                : null;                                         // them to send it back if they want more products.
        } catch (\Throwable $th) {
            return new JsonResponse([
                'error' => $th->getMessage(),
            ], $th->getCode() ?: JsonResponse::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'token'    => $token,
            'products' => $products,
        ]);
    }

    #[Route('/product/{slug}', name: 'api_product', methods: ['GET'])]
    public function getProduct(string $slug): JsonResponse
    {
        try {
            $product = $this->productService->getProduct();
        } catch (\Throwable $th) {
            return new JsonResponse([
                'error' => $th->getMessage(),
            ], $th->getCode());
        }

        return $this->json($product);
    }
}