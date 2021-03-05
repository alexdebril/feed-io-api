<?php

declare(strict_types=1);

namespace App\Storage\Provider;

use App\Storage\Repository\FeedRepository;

class FeedProvider
{

    const feedListRedisKey = 'cache:feeds:[start]:[limit]';

    const cacheTtl = 10;

    const defaultLimit = 20;

    public function __construct(
        private \Redis $redis,
        private FeedRepository $repository
    ) {}

    public function getList(int $start = 0, int $limit = self::defaultLimit)
    {
        $key = $this->getFeedListCacheKey($start, $limit);
        $feeds = $this->redis->get($key);
        if (!$feeds) {
            $cursor = $this->repository->getFeeds($start, $limit);
            $feeds = [];
            foreach ($cursor as $feed) {
                $feeds[] = $feed;
            }
            $this->redis->set($key, serialize($feeds), $this->getCacheTtl());
        } else {
            $feeds = unserialize($feeds);
        }

        return $feeds;
    }

    private function getFeedListCacheKey(int $start, int $limit): string
    {
        return str_replace(
            ['[start]', '[limit]'],
            [$start, $limit],
            self::feedListRedisKey
        );
    }

    private function getCacheTtl(): int
    {
        return self::cacheTtl;
    }
}
