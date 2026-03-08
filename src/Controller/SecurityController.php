<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository         $userRepository,
    ) {
    }

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        $email    = $payload['email'] ?? null;
        $password = $payload['password'] ?? null;

        try {
            // Verify payload content
            if (!$email || !$password) {
                throw new \Exception('Invalid payload', JsonResponse::HTTP_BAD_REQUEST);
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('Invalid email format', JsonResponse::HTTP_BAD_REQUEST);
            }

            $userExists = $this->userRepository->findOneBy(['email' => $email]);

            if ($userExists) {
                throw new \Exception('User already exists', JsonResponse::HTTP_CONFLICT);
            }
            
            // Store user in database
            $user = new User();
            $user->setEmail($email);
            $user->setPassword(password_hash($password, PASSWORD_BCRYPT));

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } catch (\Throwable $th) {
            return new JsonResponse([
                'error' => $th->getMessage(),
            ], $th->getCode());
        }

        return new JsonResponse([
            'message' => 'User registered successfully',
        ], JsonResponse::HTTP_CREATED);
    }
}