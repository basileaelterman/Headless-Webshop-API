<?php

namespace App\Controller;

use App\Entity\Product;
use App\Exception\DatabaseException;
use App\Exception\PayloadException;
use App\Exception\ProductException;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductService $productService,
    ) {
    }

    #[Route('/product', name: 'api_product', methods: ['POST'])]
    public function getProduct(Request $request): JsonResponse
    {
        $product = $this->productService->getProduct($request);

        if (!$product) {
            throw new ProductException('Product not found', JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->json($product, JsonResponse::HTTP_OK);
    }

    #[Route('/products', name: 'api_products', methods: ['POST'])]
    public function getProducts(Request $request): JsonResponse
    {
        $products = $this->productService->getProducts($request);

        if (!$products) {
            throw new ProductException('Product not found', JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->json($products, JsonResponse::HTTP_OK);
    }
}
