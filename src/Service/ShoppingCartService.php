<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\ShoppingCart;
use App\Repository\ShoppingCartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

class ShoppingCartService extends AbstractController
{
    public function __construct(
        private readonly ShoppingCartRepository $shoppingCartRepository,
    ) {
    }

    private function getShoppingCart(Request $request): ?ShoppingCart 
    {
        $payload = json_decode($request->getContent(), true) ?? null;
        $user = $this->getUser();
        $wantsToAuthenticate = $request->headers->get('Authorization') ?? null;

        if (!$user && $wantsToAuthenticate) {
            throw new \Exception('Unauthorized', JsonResponse::HTTP_UNAUTHORIZED);
        }

        $uuid = $payload['uuid'] ?? null;

        if (!$user && !$uuid) {
            throw new \Exception('A UUID is required for guests', JsonResponse::HTTP_BAD_REQUEST);
        }

        // Get the shopping cart
        if ($user) {
            $shoppingCart = $user->getShoppingCart();
        } else {
            $shoppingCart = $this->shoppingCartRepository->findOneBy(['uuid' => $uuid]);
        }

        if (!$shoppingCart) {
            $shoppingCart = $this->createShoppingCart($user ?? null);
        }

        return $shoppingCart;
    }

    private function createShoppingCart(): ?ShoppingCart
    {
        $user         = $this->getUser();
        $shoppingCart = new ShoppingCart();

        if (!$user) {
            $shoppingCart->setUuid(Uuid::v7());
        }
        
        $user?->setShoppingCart($shoppingCart);

        $this->entityManager->persist($shoppingCart);
        $this->entityManager->flush();

        return $shoppingCart;
    }

    private function resetShoppingCart(): ?ShoppingCart
    {
        $shoppingCart = $this->getShoppingCart();
        $shoppingCart->clearShoppingCart();

        $this->entityManager->persist($shoppingCart);
        $this->entityManager->flush();

        return $shoppingCart;
    }
}