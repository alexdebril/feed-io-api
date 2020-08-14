<?php declare(strict_types=1);

namespace App\Controller;

use App\Message\NewFeed;
use App\Storage\Entity\Feed\Status;
use FeedIo\FeedInterface;
use FeedIo\FeedIo;
use App\Storage\Repository\FeedRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Smells like code refactoring : many private methods in there, could be isolated in dedicated components
 */
class FeedController
{

    private FeedIo $feedIo;

    private string $allowedOrigin;

    private \Redis $redis;

    private MessageBusInterface $bus;

    public function __construct(FeedIo $feedIo, string $allowedOrigin, \Redis $redis, MessageBusInterface $bus)
    {
        $this->feedIo = $feedIo;
        $this->allowedOrigin = $allowedOrigin;
        $this->redis = $redis;
        $this->bus = $bus;
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

    public function submit(Request $request): JsonResponse
    {
        try {
            $url = $this->extractUrl($request);
            if ($ok = $this->canProcess($url)) {
                $this->bus->dispatch(
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
        return $this->extract($request, 'url');
    }

    private function extractSlug(Request $request) : string
    {
        return $this->extract($request, 'slug');
    }

    private function extract(Request $request, string $param) : string
    {
        $data = json_decode($request->getContent(), true);

        if ( isset($data[$param]) ) {
            return $data[$param];
        }

        throw new \InvalidArgumentException(sprintf('%s not found in the request', $param));
    }

    private function canProcess(string $url): bool
    {
        $url = filter_var($url, FILTER_VALIDATE_URL);
        if ( ! $url ) {
            return false;
        }

        if ($this->redis->get('url_' . $url)) {
            return false;
        }

        $this->redis->set('url_' . $url, time());

        try {
            $feed = $this->feedIo->read($url)->getFeed();
            if ( ! $feed instanceof FeedInterface ) {
                throw new \RuntimeException();
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
