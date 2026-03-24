<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

class ProductService {
    public function __construct(
        private readonly ProductRepository $productRepository,
    ) {
    }

    public function getProduct(Request $request): ?Product
    {
        $payload = json_decode($request->getContent(), true) ?? null;
        $slug    = $payload['slug'] ?? $payload['product']['slug'] ?? null;

        if (!$slug) {
            throw new \Exception('No slug found in payload', JsonResponse::HTTP_BAD_REQUEST);
        }

        $product = $this->productRepository->findOneBy(['slug' => $slug]);

        if (!$product) {
            throw new \Exception('Product not found', JsonResponse::HTTP_NOT_FOUND);
        }

        return $product;
    }
}