<?php

namespace App\Exception;

use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

class DatabaseException extends Exception
{
    public function __construct(
        string $message       = '',
        int $code             = JsonResponse::HTTP_INTERNAL_SERVER_ERROR, // 500
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $coden, $previous);
    }
}
