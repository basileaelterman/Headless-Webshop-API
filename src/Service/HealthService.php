<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class HealthService {
    public function getChecks(): array
    {
        return [
            'database' => $this->checkDatabaseConnection(),
        ];
    }

    public function checkDatabaseConnection(): array
    {
        try {
            $isOnline = $this->connection->executeQuery('SELECT 1;');
        } catch (\Throwable $th) {
            $isOnline = false;
        }

        return [
            'status' => $isOnline ? 'healthy' 
                                  : 'unhealthy',
            'message' => $isOnline ? 'Connected'
                                   : 'Unable to reach database',
        ];
    }
}
