<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class UserController extends AbstractController
{
    #[Route('/user', name: 'api_user', methods: ['POST'])]
    public function get(): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json([
                'error' => 'Unauthorized',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'user' => $user,
        ], 200, [], [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['userIdentifier', 'id'],
        ]);
    }
}