<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly ProductService    $productService,
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
            $product = $this->productService->getProduct();
        } catch (\Throwable $th) {
            return new JsonResponse([
                'error' => $th->getMessage(),
            ], $th->getCode());
        }

        return $this->json($product);
    }
}