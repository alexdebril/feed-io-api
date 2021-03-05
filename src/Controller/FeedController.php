<?php

declare(strict_types=1);

namespace App\Controller;

use App\Message\NewFeed;
use App\Storage\Entity\Feed\Status;
use App\Storage\Provider\FeedProvider;
use App\Storage\Repository\FeedRepository;
use FeedIo\FeedInterface;
use FeedIo\FeedIo;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Smells like code refactoring : many private methods in there, could be isolated in dedicated components.
 */

#[Route('/feeds', name: 'feeds_')]
class FeedController
{

    public function __construct(
        private FeedIo $feedIo,
        private string $allowedOrigin,
        private \Redis $redis
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

    #[Route('/submit', name: 'submit', methods: ['POST'])]
    public function submit(Request $request, MessageBusInterface $bus): JsonResponse
    {
        try {
            $url = $this->extractUrl($request);
            if ($ok = $this->canProcess($url)) {
                $bus->dispatch(
                    new NewFeed($url)
                );
            }

            return $this->newJsonResponse(
                ['ok' => $ok]
            );
        } catch (\Exception $e) {
            return $this->newJsonError($e);
        }
    }

    #[Route('/accept', name: 'accept', methods: ['POST'])]
    public function accept(Request $request, FeedRepository $repository): JsonResponse
    {
        try {
            $feed = $repository->findOneBySlug($this->extractSlug($request));
            $feed->setStatus(
                new Status(Status::ACCEPTED)
            );
            $feed->setLanguage($this->extract($request, 'language'));
            $repository->save($feed);

            return $this->newJsonResponse(['ok' => true]);
        } catch (\Exception $e) {
            return $this->newJsonError($e);
        }
    }

    #[Route('/list/{start<\d+>?0}/{limit<\d+>?10}', name: 'list', methods: ['GET'])]
    public function getList(int $start, int $limit, FeedProvider $provider): JsonResponse
    {
        try {
            return $this->newJsonResponse([
                'feeds' => $provider->getList($start, $limit),
                'count' => $provider->getCount(),
            ]);
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

    private function extractSlug(Request $request): string
    {
        return $this->extract($request, 'slug');
    }

    private function extract(Request $request, string $param): string
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data[$param])) {
            return $data[$param];
        }

        throw new \InvalidArgumentException(sprintf('%s not found in the request', $param));
    }

    private function canProcess(string $url): bool
    {
        $url = filter_var($url, FILTER_VALIDATE_URL);
        if (!$url) {
            return false;
        }

        if ($this->redis->get('url_'.$url)) {
            return false;
        }

        $this->redis->set('url_'.$url, time());

        try {
            $feed = $this->feedIo->read($url)->getFeed();
            if (!$feed instanceof FeedInterface) {
                throw new \RuntimeException();
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
