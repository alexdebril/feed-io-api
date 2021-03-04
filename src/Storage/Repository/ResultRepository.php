<?php


namespace App\Storage\Repository;


use App\Storage\Entity\Feed;
use App\Storage\Entity\Result;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\Cursor;
use MongoDB\InsertOneResult;

class ResultRepository extends AbstractRepository
{
    protected function getCollectionName(): string
    {
        return 'results';
    }

    public function getResults(Feed $feed, int $start, int $limit): Cursor
    {
        return $this->getCollection()->find(['feedId' => $feed->getId()],
            [
                'typeMap' => ['root' => Result::class],
                'sort' => ['eventDate' => -1],
                'skip' => $start,
                'limit' => $limit,
            ]
        );
    }

    public function getAveragedStats(Feed $feed, \DateTime $dateTime): \Traversable
    {
        return $this->getCollection()->aggregate([
            ['$match' => ['feedId' => $feed->getId(), 'eventDate' => ['$gt' => new UTCDateTime($dateTime)]]],
            ['$group' => [
                '_id' => '$feedId',
                'duration' => ['$avg' => '$durationInMs'],
                'count' => ['$avg' => '$itemCount']
            ]]
        ]);
    }

    public function getHttpStats(Feed $feed, \DateTime $dateTime)
    {
        return $this->getCollection()->aggregate([
            ['$match' => ['feedId' => $feed->getId(), 'eventDate' => ['$gt' => new UTCDateTime($dateTime)]]],
            ['$group' => [
                '_id' => '$statusCode',
                'count' => ['$sum' => 1],
            ]]
        ]);
    }

    public function getSuccessStats(Feed $feed, \DateTime $dateTime)
    {
        return $this->getCollection()->aggregate([
            ['$match' => ['feedId' => $feed->getId(), 'eventDate' => ['$gt' => new UTCDateTime($dateTime)]]],
            ['$group' => [
                '_id' => '$success',
                'count' => ['$sum' => 1],
            ]]
        ]);
    }

    public function save(Result $result): InsertOneResult
    {
        return $this->getCollection()->insertOne($result);
    }
}
