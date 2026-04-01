<?php

namespace App\Service;

use App\DTO\PayloadDTO;
use App\Entity\Product;
use App\Exception\DatabaseException;
use App\Exception\PayloadException;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

class ProductService {
    public const MIN_QUANTITY = 1;
    public const MAX_QUANTITY = 50;

    public function __construct(
        private readonly ProductRepository $productRepository,
    ) {
    }

    public function getProduct(Request $request): ?Product
    {
        $payload = json_decode($request->getContent(), true) ?? null;

        if (!$payload) {
            throw new PayloadException('Invalid payload', JsonResponse::HTTP_BAD_REQUEST);
        }

//      $id   = $payload['id'] ?? null;
        $slug = $payload['slug'] ?? null;

        // Validate input
        $payloadDTO = new PayloadDTO();
//      $payloadDTO->setId($id);
        $payloadDTO->setSlug($slug);

        $violations = $payloadDTO->getViolations();

        if ($violations) {
            throw new PayloadException('Invalid variables in payload', JsonResponse::HTTP_BAD_REQUEST);
        }

        // Fetch the product
        $product = null;

        if ($slug) {
            $product = $this->getProductBySlug($slug);
//      } else if ($id) {
//          $product = $this->getProductById($id);
        } else {
            throw new PayloadException('No product identificator found in payload', JsonResponse::HTTP_BAD_REQUEST);
        }

        return $product;
    }

    public function getProducts(Request $request): ?array
    {
        $payload = json_decode($request->getContent(), true) ?? null;

        if (!$payload) {
            throw new PayloadException('Invalid payload', JsonResponse::HTTP_BAD_REQUEST);
        }

        $category = $payload['category'] ?? null;
        $minPrice = $payload['minPrice'] ?? $payload['min'] ?? null;
        $maxPrice = $payload['maxPrice'] ?? $payload['max'] ?? null;
        $quantity = $payload['quantity'] ?? null;
        $query    = $payload['query'] ?? $payload['q'] ?? null;
        $token    = $payload['token'] ?? null;

        // Validate input
        $payloadDTO = new PayloadDTO();
        $payloadDTO->setCategory($category);
        $payloadDTO->setMinPrice($minPrice);
        $payloadDTO->setMaxPrice($maxPrice);
        $payloadDTO->setQuantity($quantity);
        $payloadDTO->setQuery($query);
        $payloadDTO->setToken($token);

        $violations = $payloadDTO->getViolations();

        if ($violations) {
            throw new PayloadException('Invalid variables in payload', JsonResponse::HTTP_BAD_REQUEST); // Store violations in exception array by using a new method?
        }

        // Fetch the products
        $lastId   = $token ? base64_decode($token, true) 
                           : null;
        $products = null;

        if ($minPrice && $maxPrice) {
            $products = $this->getProductsInPriceRange($minPrice, $maxPrice, $quantity, $lastId);
        } else if ($category) {
            $products = $this->getProductsByCategory($category, $quantity, $lastId);
        } else if ($query) {
            $products = $this->getProductsByQuery($query, $quantity, $lastId);
        } else {
            $products = $this->getLatestProducts($quantity, $lastId);
        }

        if ($products) {
            $hasMore  = count($products) > $quantity;

            if ($hasMore) {
                array_pop($products);
            }

            $lastProduct = end($products);
            $token = ($hasMore && $lastProduct) ? base64_encode((string) $lastProduct->getId()) // We send a token back to the clientside and expect
                                                : null;    
        }

        return $products ? [
            'products' => $products,
            'token'    => $token,
        ] : null;
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

    public function getProductsByCategory(string $category, int $quantity, ?int $lastId = null): ?ArrayCollection
    {
        $products = null;

        try {
            $query = $this->productRepository->createQueryBuilder('product')
                          ->orderBy('product.id', 'DESC')
                          ->setMaxResults($quantity + 1)
                          ->andWhere('product.category = :category')
                          ->setParameter('category', $category);

            if ($lastId) {
                $query->andWhere('product.id < :lastId')
                      ->setParameter('lastId', $lastId);
            }

            $products = $query->getQuery()
                              ->getResult();
        } catch (\Throwable $th) {
            throw new DatabaseException($th->getMessage(), JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $products;
    }

    public function getProductsInPriceRange(float $minPrice, float $maxPrice, int $quantity, ?int $lastId = null): ?array
    {
        $products = null;

        try {
            $query = $this->productRepository->createQueryBuilder('product')
                          ->orderBy('product.id', 'DESC')
                          ->setMaxResults($quantity + 1)
                          ->andWhere('product.price >= :minPrice')
                          ->andWhere('product.price <= :maxPrice')
                          ->setParameter('minPrice', $minPrice)
                          ->setParameter('maxPrice', $maxPrice);

            if ($lastId) {
                $query->andWhere('product.id < :lastId')
                      ->setParameter('lastId', $lastId);
            }

            $products = $query->getQuery()
                              ->getResult();
        } catch (\Throwable $th) {
            throw new DatabaseException($th->getMessage(), JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $products;
    }

    public function getLatestProducts(int $quantity, ?int $lastId = null): ?array {
        $products = null;

        try {
            $query = $this->productRepository->createQueryBuilder('product')
                          ->orderBy('product.id', 'DESC')
                          ->setMaxResults($quantity + 1);

            if ($lastId) {
                $query->andWhere('product.id < :lastId')
                      ->setParameter('lastId', $lastId);
            }

            $products = $query->getQuery()
                              ->getResult();
        } catch (\Throwable $th) {
            throw new DatabaseException($th->getMessage(), JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $products;
    }

    public function getProductsByQuery(string $query, int $quantity, ?int $lastId): ?array
    {
        $products = null;

        try {
            $queryBuilder = $this->productRepository->createQueryBuilder('product')
                                 ->andWhere('product.name LIKE :query')
                                 ->setParameter('query', '%' . $query . '%')
                                 ->orderBy('product.id', 'DESC')
                                 ->setMaxResults($quantity + 1);

            if ($lastId) {
                $queryBuilder->andWhere('product.id < :lastId')
                             ->setParameter('lastId', $lastId);
            }

            $products = $queryBuilder->getQuery()
                                     ->getResult();
        } catch (\Throwable $th) {
            throw new DatabaseException($th->getMessage(), JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $products;
    }
}
