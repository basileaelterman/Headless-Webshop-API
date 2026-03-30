<?php

namespace App\Controller;

use App\DTO\UserDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SecurityController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserService            $userService,
    ) {
    }

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, ValidatorInterface $validatorInterface): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?? null;

        if (!$payload) {
            return new JsonResponse([
                'error' => 'Invalid payload',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $email    = $payload['email'] ?? null;
        $password = $payload['password'] ?? null;

        // Validate input
        $userDTO = new UserDTO();
        $userDTO->setEmail($email);
        $userDTO->setPassword($password);
        
        $violations = $userDTO->getViolations();

        if ($violations) {
            return new JsonResponse([
                'error' => 'Invalid payload',
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            // Check if email isn't already taken
            $userExists = $this->userService->getUserByEmail($email);

            if ($userExists) {
                return new JsonResponse([
                    'error' => 'User already exists',
                ], JsonResponse::HTTP_CONFLICT);
            }

            // Create and store the user
            $user = new User();
            $user->setEmail($email);
            $user->setPassword(password_hash($password, PASSWORD_BCRYPT));

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } catch (\Throwable $th) {
            return new JsonResponse([
                'error' => 'An unexpected error occured',
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'message' => 'User registered successfully',
        ], JsonResponse::HTTP_CREATED);
    }
}