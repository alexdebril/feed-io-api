<?php

namespace App\Controller;

use \FeedIo\FeedIo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FeedController
{
    /**
     * @var \FeedIo\FeedIo
     */
    private $feedIo;

    private $allowedOrigin;

    /**
     * @param FeedIo $feedIo        feed-io instance
     * @param string $allowedOrigin
     */
    public function __construct(FeedIo $feedIo, string $allowedOrigin)
    {
        $this->feedIo = $feedIo;
        $this->allowedOrigin = $allowedOrigin;
    }

    /**
     *
     */
    public function consume(Request $request) : JsonResponse
    {
        try {
            return new JsonResponse(
                $this->feedIo->read($this->extractUrl($request))->getFeed(),
                200,
                [
                    'Access-Control-Allow-Origin' => $this->allowedOrigin
                ]
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                [
                    'type' => get_class($e),
                    'title' => $e->getMessage(),
                ],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                [
                    'Content-Type' => 'application/problem+json'
                ]
            );
        }
    }

    public function discover(Request $request) : JsonResponse
    {
        try {
            return new JsonResponse(
                $this->feedIo->discover($this->extractUrl($request))
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                [
                    'type' => get_class($e),
                    'title' => $e->getMessage(),
                ],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                [
                    'Content-Type' => 'application/problem+json'
                ]
            );
        }
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
