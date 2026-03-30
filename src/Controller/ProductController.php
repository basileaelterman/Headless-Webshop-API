<?php

namespace App\Controller;

use App\Entity\Product;
use App\Exception\DatabaseException;
use App\Exception\PayloadException;
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
    public function getProducts(): JsonResponse
    {
        try {
            $products = $this->productService->getProducts();
        } catch (DatabaseException $error) {
            return new JsonResponse([
                'error' => 'An unexpected error has occured',
            ], $error->getCode() ?: JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (PayloadException $error) {
            return new JsonResponse([
                'error' => $error->getMessage(),
            ], $error->getCode() ?: JsonResponse::HTTP_BAD_REQUEST);
        }

        return $this->json($products, JsonResponse::HTTP_OK);
    }

    #[Route('/product', name: 'api_product', methods: ['GET'])]
    public function getProduct(): JsonResponse
    {
        try {
            $product = $this->productService->getProduct();
        } catch (DatabaseException $error) {
            return new JsonResponse([
                'error' => 'An unexpected error has occured',
            ], $error->getCode() ?: JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (PayloadException $error) {
            return new JsonResponse([
                'error' => $error->getMessage(),
            ], $error->getCode() ?: JsonResponse::HTTP_BAD_REQUEST);
        }

        return $this->json($product, JsonResponse::HTTP_OK);
    }
}