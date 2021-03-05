<?php declare(strict_types=1);


namespace App\Storage\Provider;


use App\Storage\Repository\FeedRepository;
use App\Storage\Repository\ResultRepository;

class ResultProvider
{
    const resultRedisKey = 'cache:results:[slug]:[start]:[limit]';

    const statsRedisKey = 'cache:stats:[slug]:[days]';

    const cacheTtl = 60;

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

    public function getStats(string $slug, int $days)
    {
        $since = new \DateTime(sprintf('-%sdays', $days));
        $key = $this->getStatsCacheKey($slug, $days);
        $stats = $this->redis->get($key);
        if (!$stats) {
            $stats = ['http' => [], 'avg' => [], 'success' => []];
            $feed = $this->feedRepository->findOneBySlug($slug);
            $avg = iterator_to_array($this->resultRepository->getAveragedStats($feed, $since));
            $average = isset($avg[0]) ? $avg[0] : ['duration' => 0, 'count' => 0];
            $stats['avg'] = [
                'duration' => round($average['duration'], 2),
                'count' => round($average['count'], 2),
            ];
            foreach ($this->resultRepository->getHttpStats($feed, $since) as $result) {
                $stats['http'][$result['_id']] = $result['count'];
            }
            foreach ($this->resultRepository->getSuccessStats($feed, $since) as $result) {
                $stats['success'][$result['_id']?'true':'false'] = $result['count'];
            }
            $this->redis->set($key, serialize($stats), $this->getCacheTtl());
        } else {
            $stats = unserialize($stats);
        }
        return $stats;
    }

    private function getCacheKey(string $slug, int $start, int $limit): string
    {
        return str_replace(
            ['[slug]', '[start]', '[limit]'],
            [$slug, $start, $limit],
            self::resultRedisKey
        );
    }

    private function getStatsCacheKey(string $slug, int $days): string
    {
        return str_replace(['[slug]', '[days]'], [$slug, $days], self::statsRedisKey);
    }

    private function getCacheTtl(): int
    {
        return self::cacheTtl;
    }
}
