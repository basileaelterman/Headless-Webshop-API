<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $productRepository,
    ) {
    }

    #[Route('/products', name: 'api_products', methods: ['GET'])]
    public function getProducts(): JsonResponse
    {
        try {
            $products = $this->productRepository->findAll();
        } catch (\Throwable $th) {
            return new JsonResponse([
                'error' => $th->getMessage(),
            ], $th->getCode());
        }

        return $this->json($products);
    }

    #[Route('/product/{slug}', name: 'api_product', methods: ['GET'])]
    public function getProduct(string $slug): JsonResponse
    {
        try {
            $product = $this->productRepository->findOneBy(['slug' => $slug]);
        } catch (\Throwable $th) {
            return new JsonResponse([
                'error' => $th->getMessage(),
            ], $th->getCode());
        }

        if (!$product) {
            return new JsonResponse([
                'error' => 'Product not found',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->json($product);
    }
}