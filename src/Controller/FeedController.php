<?php

declare(strict_types=1);

namespace App\Controller;

use FeedIo\FeedIo;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/feed', name: 'feed_')]
class FeedController
{

    public function __construct(
        private FeedIo $feedIo,
        private string $allowedOrigin,
    ) {}

    #[Route('/consume', name: 'consume', methods: ['POST'])]
    public function consume(Request $request): JsonResponse
    {
        try {
            return $this->newJsonResponse(
                $this->feedIo->read(
                    $this->extractUrl($request)
                )->getFeed()
            );
        } catch (\Exception $e) {
            return $this->newJsonError($e);
        }
    }

    #[Route('/discover', name: 'discover', methods: ['POST'])]
    public function discover(Request $request): JsonResponse
    {
        try {
            return $this->newJsonResponse(
                $this->feedIo->discover(
                    $this->extractUrl($request)
                )
            );
        } catch (\Exception $e) {
            return $this->newJsonError($e);
        }
    }

    private function newJsonResponse($data): JsonResponse
    {
        return new JsonResponse(
            $data,
            200,
            [
                'Access-Control-Allow-Origin' => $this->allowedOrigin,
            ]
        );
    }

    private function newJsonError(\Throwable $exception): JsonResponse
    {
        return new JsonResponse(
            [
                'type' => get_class($exception),
                'title' => $exception->getMessage(),
            ],
            JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            [
                'Content-Type' => 'application/problem+json',
            ]
        );
    }

    private function extractUrl(Request $request): string
    {
        return $this->extract($request, 'url');
    }

    private function extract(Request $request, string $param): string
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data[$param])) {
            return $data[$param];
        }

        throw new \InvalidArgumentException(sprintf('%s not found in the request', $param));
    }

}
