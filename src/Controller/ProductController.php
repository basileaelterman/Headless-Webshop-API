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

    #[Route('/product', name: 'api_product', methods: ['GET'])]
    public function getProduct(): JsonResponse
    {
        try {
            $product = $this->productService->getProduct();

            if (!$product) {
                throw new ProductException('Product not found', JsonResponse::HTTP_NOT_FOUND);
            }
        } catch (DatabaseException $error) {
            return new JsonResponse([
                'error' => 'An unexpected error has occured',
            ], $error->getCode() ?: JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (PayloadException $error) {
            return new JsonResponse([
                'error' => $error->getMessage(),
            ], $error->getCode() ?: JsonResponse::HTTP_BAD_REQUEST);
        } catch (ProductException $error) {
            return new JsonResponse([
                'error' => $error->getMessage(),
            ], $error->getCode());
        } catch (Throwable $error) {
            return new JsonResponse([
                'error' => 'An unknown error has occured',
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json($product, JsonResponse::HTTP_OK);
    }

    #[Route('/products', name: 'api_products', methods: ['GET'])]
    public function getProducts(Request $request): JsonResponse
    {
        try {
            $products = $this->productService->getProducts($request);

            if (!$product) {
                throw new ProductException('Product not found', JsonResponse::HTTP_NOT_FOUND);
            }
        } catch (DatabaseException $error) {
            return new JsonResponse([
                'error' => 'An unexpected error has occured',
            ], $error->getCode() ?: JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (PayloadException $error) {
            return new JsonResponse([
                'error' => $error->getMessage() ?: 'Invalid payload',
            ], $error->getCode() ?: JsonResponse::HTTP_BAD_REQUEST);
        } catch (Throwable $error) {
            return new JsonResponse([
                'error' => 'An unknown error has occured',
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json($products, JsonResponse::HTTP_OK);
    }
}
