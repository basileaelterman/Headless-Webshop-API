<?php

namespace App\Controller;

use App\Service\HealthService;
use \DateTimeImmutable;
use DateTimeInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class HealthController extends AbstractController
{
    public function __construct(
        private readonly HealthService $healthService,
    ) {
    }

    #[Route('/health', name: 'app_health', methods: ['GET'])]
    public function show(): JsonResponse
    {
        $checks = $this->healthService->getChecks();
        $isHealthy = $this->allChecksPass($checks);

        return new JsonResponse(
            data: [
                'status'    => $isHealthy ? 'healthy' : 'unhealthy',
                'timestamp' => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
                'checks'    => $checks,
            ],
            status: $isHealthy ? JsonResponse::HTTP_OK
                               : JsonResponse::HTTP_SERVICE_UNAVAILABLE,
            headers: [
                'Cache-Control' => 'no-store',
            ],
        );
    }

    private function allChecksPass(array $checks): bool
    {
        return !in_array('unhealthy', array_column($checks, 'status'), true);
    }
}
