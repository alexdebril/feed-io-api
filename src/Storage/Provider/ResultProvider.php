<?php declare(strict_types=1);


namespace App\Storage\Provider;


use App\Storage\Repository\FeedRepository;
use App\Storage\Repository\ResultRepository;

class ResultProvider
{
    const resultRedisKey = 'cache:results:[slug]:[start]:[limit]';

    const cacheTtl = 60 * 3;

    const defaultLimit = 20;

    public function __construct(
        private \Redis $redis,
        private FeedRepository $feedRepository,
        private ResultRepository $resultRepository,
    ) {}

    public function getResults(string $slug, int $start = 0, int $limit = self::defaultLimit)
    {
        $key = $this->getCacheKey($slug, $start, $limit);
        $results = $this->redis->get($key);
        if (!$results) {
            $feed = $this->feedRepository->findOneBySlug($slug);
            $cursor = $this->resultRepository->getResults($feed, $start, $limit);
            $results = [];
            foreach ($cursor as $result) {
                $results[] = $result;
            }
            $this->redis->set($key, serialize($results), $this->getCacheTtl());
        } else {
            $results = unserialize($results);
        }

        return $results;
    }

    private function getCacheKey(string $slug, int $start, int $limit): array|string
    {
        return str_replace(
            ['[slug]', '[start]', '[limit]'],
            [$slug, $start, $limit],
            self::resultRedisKey
        );
    }

    private function getCacheTtl(): int
    {
        return self::cacheTtl;
    }
}
