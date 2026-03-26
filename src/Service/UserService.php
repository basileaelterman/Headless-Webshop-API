<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserService {
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    public function getUserByEmail(string $email): ?User
    {
        try {
            $user = $this->userRepository->findOneBy(['email' => $email]);

            if (!$user) {
                throw new \Exception('User not found', JsonResponse::HTTP_NOT_FOUND);
            }
        } catch (\Throwable $th) {
            return null;
        }

        return $user;
    }
}
