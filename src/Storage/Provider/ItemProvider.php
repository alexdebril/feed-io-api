<?php


namespace App\Storage\Provider;


use App\Storage\Repository\ItemRepository;
use Debril\RssAtomBundle\Provider\FeedProviderInterface;
use FeedIo\Feed;
use FeedIo\FeedInterface;
use Symfony\Component\HttpFoundation\Request;

class ItemProvider implements FeedProviderInterface
{

    const itemRedisKey = 'cache:items:[limit]';

    const cacheTtl = 60 * 3;

    const defaultLimit = 20;

    public function __construct(
        private ItemRepository $repository,
        private \Redis $redis,
    ){}

    public function getFeed(Request $request): FeedInterface
    {
        $feed = new Feed();
        $feed->setTitle('feed-io API');
        $feed->setDescription('last items ingested by the updater. This feed exists only to show the system\'s health');
        $first = true;
        foreach($this->getItems() as $item) {
            if ($first) {
                $feed->setLastModified($item->getLastModified());
                $first = false;
            }
            $feed->add($item);
        }
        return $feed;
    }

    public function getItems(int $limit = self::defaultLimit)
    {
        $key = $this->getCacheKey($limit);
        $items = $this->redis->get($key);
        if (!$items) {
            $cursor = $this->repository->getItems($limit);
            $items = [];
            foreach ($cursor as $item) {
                $items[] = $item;
            }
            $this->redis->set($key, serialize($items), $this->getCacheTtl());
        } else {
            $items = unserialize($items);
        }
        return $items;
    }

    private function getCacheKey(int $limit): array|string
    {
        return str_replace(['[limit]'], [$limit], self::itemRedisKey);
    }

    private function getCacheTtl(): int
    {
        return self::cacheTtl;
    }

}
