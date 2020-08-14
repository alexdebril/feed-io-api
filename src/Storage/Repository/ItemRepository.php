<?php declare(strict_types=1);

namespace App\Storage\Repository;

use App\Storage\Entity\Item;
use MongoDB\BSON\ObjectIdInterface;
use MongoDB\Driver\Cursor;
use MongoDB\InsertOneResult;

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
     * @param ObjectIdInterface $feedId
     * @param int $start
     * @param int $limit
     * @return Cursor<Item>
     */
    public function getItemsFromFeed(ObjectIdInterface $feedId, int $start = 0, int $limit = 10): Cursor
    {
        return $this->getCollection()->find(
            ['feedId' => $feedId],
            [
                'typeMap' => ['root' => Item::class],
                'skip' => $start,
                'limit' => $limit,
                'sort' => ['lastModified' => -1]
            ]
        );
    }

    public function save(Item $item): InsertOneResult
    {
        if (is_null($item->getPublicId())) {
            throw new \UnexpectedValueException("publicId cannot be null");
        }
        return $this->getCollection()->insertOne($item);
    }

    protected function getCollectionName(): string
    {
        return 'items';
    }
}
