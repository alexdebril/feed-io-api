<?php

declare(strict_types=1);

namespace App\Storage\Repository;

use App\Storage\Entity\Item;
use MongoDB\BSON\ObjectIdInterface;
use MongoDB\Driver\Cursor;
use MongoDB\InsertOneResult;
use MongoDB\UpdateResult;

class ItemRepository extends AbstractRepository
{
    public function findOne(ObjectIdInterface $objectId): ? Item
    {
        $item = $this->getCollection()->findOne(
            ['_id' => $objectId],
            ['typeMap' => ['root' => Item::class]]
        );

        if ($item instanceof Item) {
            return $item;
        }

        return null;
    }

    /**
     * @return Cursor<Item>
     */
    public function getItems(int $limit = 10): Cursor
    {
        return $this->getCollection()->find(
            [],
            [
                'typeMap' => ['root' => Item::class],
                'limit' => $limit,
                'sort' => ['lastModified' => -1],
            ]
        );
    }

    public function save(Item $item): UpdateResult
    {
        if (is_null($item->getPublicId())) {
            throw new \UnexpectedValueException('publicId cannot be null');
        }

        return $this->getCollection()->updateOne(
            [
                'feedId' => $item->getFeedId(),
                'publicId' => $item->getPublicId()
            ],
            ['$set' => $item],
            ['upsert' => true]
        );
    }

    protected function getCollectionName(): string
    {
        return 'items';
    }
}
