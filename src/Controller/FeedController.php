<?php declare(strict_types=1);

namespace App\Controller;

use \FeedIo\FeedIo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FeedController
{

    private FeedIo $feedIo;

    private string $allowedOrigin;

    public function __construct(FeedIo $feedIo, string $allowedOrigin)
    {
        $this->feedIo = $feedIo;
        $this->allowedOrigin = $allowedOrigin;
    }

    public function consume(Request $request) : JsonResponse
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

    public function discover(Request $request) : JsonResponse
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
                'Access-Control-Allow-Origin' => $this->allowedOrigin
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
                'Access-Control-Allow-Origin' => $this->allowedOrigin,
                'Content-Type' => 'application/problem+json'
            ]
        );
    }

    private function extractUrl(Request $request) : string
    {
        $data = json_decode($request->getContent(), true);

        if ( isset($data['url']) ) {
            return $data['url'];
        }

        throw new \InvalidArgumentException("No url found in the request");
    }
}
