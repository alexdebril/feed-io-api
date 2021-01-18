<?php


namespace App\Storage\Repository;


use App\Storage\Entity\Result;
use MongoDB\InsertOneResult;

class ResultRepository extends AbstractRepository
{
    protected function getCollectionName(): string
    {
        return 'results';
    }

    public function save(Result $result): InsertOneResult
    {
        return $this->getCollection()->insertOne($result);
    }
}
