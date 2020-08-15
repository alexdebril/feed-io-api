<?php

declare(strict_types=1);

namespace App\Storage\Repository;

use App\Storage\Entity\Feed;
use MongoDB\BSON\ObjectIdInterface;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\Cursor;
use MongoDB\UpdateResult;

class FeedRepository extends AbstractRepository
{
    public function findOne(ObjectIdInterface $objectId): ? Feed
    {
        $feed = $this->getCollection()->findOne(
            ['_id' => $objectId],
            ['typeMap' => ['root' => Feed::class]]
        );

        if ($feed instanceof Feed) {
            return $feed;
        }

        return null;
    }

    public function findOneBySlug(string $slug): ? Feed
    {
        $feed = $this->getCollection()->findOne(
            ['slug' => $slug],
            ['typeMap' => ['root' => Feed::class]]
        );

        if ($feed instanceof Feed) {
            return $feed;
        }

        return null;
    }

    /**
     * @param array<string> $statuses
     *
     * @return Cursor<Feed>
     */
    public function getFeedsToUpdate(array $statuses = [Feed\Status::ACCEPTED, Feed\Status::APPROVED]): Cursor
    {
        return $this->getCollection()->find(
            [
                'nextUpdate' => ['$lte' => new UTCDateTime()],
                'status' => ['$in' => $statuses],
            ],
            ['typeMap' => ['root' => Feed::class]]
        );
    }

    /**
     * @return Cursor<Feed>
     */
    public function getFeedsByStatus(string $status): Cursor
    {
        return $this->getCollection()->find(
            ['status' => $status],
            ['typeMap' => ['root' => Feed::class]]
        );
    }

    /**
     * @return Cursor<Feed>
     */
    public function getFeeds(int $start, int $limit): Cursor
    {
        return $this->getCollection()->find([],
            [
                'typeMap' => ['root' => Feed::class],
                'skip' => $start,
                'limit' => $limit,
            ]
        );
    }

    public function save(Feed $feed): UpdateResult
    {
        if (is_null($feed->getUrl())) {
            throw new \UnexpectedValueException('feed URL cannot be null');
        }

        return $this->getCollection()->updateOne(
            ['url' => $feed->getUrl()],
            ['$set' => $feed],
            ['upsert' => true]
        );
    }

    protected function getCollectionName(): string
    {
        return 'feeds';
    }
}
