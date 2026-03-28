<?php

namespace App\Service;

use App\Exception\DatabaseException;
use App\Exception\PayloadException;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

class ProductService {
    public function __construct(
        private readonly ProductRepository $productRepository,
    ) {
    }

    public function getProduct(Request $request): Product|JsonResponse|null
    {
        $payload = json_decode($request->getContent(), true) ?? null;

        $slug     = $payload['slug'] ?? null;
//      $id       = $payload['id'] ?? null;
        $minPrice = $payload['minPrice'] ?? $payload['min'] ?? null;
        $maxPrice = $payload['maxPrice'] ?? $payload['max'] ?? null;

        try {
            // TODO: validate everything using DTOs
            if ($slug) {
                $product = $this->getProductBySlug($slug);
//          } else if ($id) {
//              $product = $this->getProductById($id);
            } else if ($minPrice && $maxPrice) {
                if ($minPrice > $maxPrice) {
                    throw new PayloadException('Minimum price cannot be larger than maximum price', JsonResponse::HTTP_BAD_REQUEST);
                }
                if ($minPrice < 0) {
                    throw new PayloadException('Minimum price must be a positive number', JsonResponse::HTTP_BAD_REQUEST);
                }

                $products = $this->getProductsInPriceRange($minPrice, $maxPrice);
            } else {
                throw new PayloadException('No product identificator found in payload', JsonResponse::HTTP_BAD_REQUEST);
            }
        } catch (PayloadException $error) {
            return new JsonResponse([
                'error' => $error->getMessage(),
            ], $error->getCode() ?: JsonResponse::HTTP_BAD_REQUEST);
        } catch (DatabaseException $error) {
            return new JsonResponse([
                'error' => 'An unexpected error has occured',
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $product;
    }

    public function getProductBySlug(string $slug): ?Product
    {
        try {
            $product = $this->productRepository->findOneBy([
                'slug' => $slug,
            ]);
        } catch (\Throwable $th) {
            throw new DatabaseException($th->getMessage(), JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $product ?: null;
    }

    public function getProductById(int $id): ?Product
    {
        try {
            $product = $this->productRepository->findOneBy([
                'id' => $id,
            ]);
        } catch (\Throwable $th) {
            throw new DatabaseException($th->getMessage(), JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $product ?: null;
    }

    public function getProductsByCategory(string $category): ?ArrayCollection
    {
        try {
            $products = $this->productRepository->findBy([
                'category' => $category,
            ]);
        } catch (\Throwable $th) {
            throw new DatabaseException($th->getMessage(), JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $products ? new ArrayCollection($products)
                         : null;
    }

    public function getProductsInPriceRange(float $minPrice, float $maxPrice): ?ArrayCollection
    {
        try {
            $query = $this->createQueryBuilder('product')
                          ->andWhere('product.price >= :minPrice')
                          ->andWhere('product.price <= :maxPrice')
                          ->setParameter('minPrice', $minPrice)
                          ->setParameter('maxPrice', $maxPrice);

            $products = $query->getQuery()
                              ->getResult();
        } catch (\Throwable $th) {
            throw new DatabaseException($th->getMessage(), JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $products ? new ArrayCollection($products)
                         : null;
    }
}