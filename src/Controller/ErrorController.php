<?php declare(strict_types=1);


namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ErrorController
{
    public function show(\Throwable $exception, LoggerInterface $logger): JsonResponse
    {
        $logger->error('error handled by ErrorController', ['error' => $exception->getMessage()]);
        $status = $exception instanceof NotFoundHttpException ? 404:500;
        return $this->newJsonResponse($status, [
            'status' => $status,
            'error' => $exception->getMessage()
        ]);
    }

    private function newJsonResponse(int $status, $data): JsonResponse
    {
        return new JsonResponse(
            $data,
            $status,
        );
    }
}
