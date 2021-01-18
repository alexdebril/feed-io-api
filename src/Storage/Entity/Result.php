<?php

declare(strict_types=1);

namespace App\Storage\Entity;



use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Serializable;
use MongoDB\BSON\Unserializable;
use MongoDB\BSON\UTCDateTime;

class Result implements Serializable, Unserializable
{

    protected ?ObjectId $id;

    protected ObjectId $feedId;

    protected \DateTime $eventDate;

    protected \DateTime $lastModified;

    protected bool $success;

    protected int $durationInMs;

    protected int $itemCount;

    protected int $statusCode;

    /**
     * @return ObjectId
     */
    public function getFeedId(): ObjectId
    {
        return $this->feedId;
    }

    /**
     * @param ObjectId $feedId
     * @return Result
     */
    public function setFeedId(ObjectId $feedId): Result
    {
        $this->feedId = $feedId;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEventDate(): \DateTime
    {
        return $this->eventDate;
    }

    /**
     * @param \DateTime $eventDate
     * @return Result
     */
    public function setEventDate(\DateTime $eventDate): Result
    {
        $this->eventDate = $eventDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastModified(): \DateTime
    {
        return $this->lastModified;
    }

    /**
     * @param \DateTime $lastModified
     * @return Result
     */
    public function setLastModified(\DateTime $lastModified): Result
    {
        $this->lastModified = $lastModified;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @param bool $success
     * @return Result
     */
    public function setSuccess(bool $success): Result
    {
        $this->success = $success;
        return $this;
    }

    /**
     * @return int
     */
    public function getDurationInMs(): int
    {
        return $this->durationInMs;
    }

    /**
     * @param int $durationInMs
     * @return Result
     */
    public function setDurationInMs(int $durationInMs): Result
    {
        $this->durationInMs = $durationInMs;
        return $this;
    }

    /**
     * @return int
     */
    public function getItemCount(): int
    {
        return $this->itemCount;
    }

    /**
     * @param int $itemCount
     * @return Result
     */
    public function setItemCount(int $itemCount): Result
    {
        $this->itemCount = $itemCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     * @return Result
     */
    public function setStatusCode(int $statusCode): Result
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function bsonSerialize(): array
    {
        $properties = get_object_vars($this);
        unset($properties['id']);

        foreach ($properties as $name => $property) {
            if ($property instanceof \DateTime) {
                $properties[$name] = new UTCDateTime($property);
            }
        }

        return $properties;
    }

    /**
     * @param array<mixed> $data
     */
    public function bsonUnserialize(array $data): void
    {
        $this->id = $data['_id'];
        $this->setFeedId($data['feedId'])
            ->setDurationInMs($data['durationInMs'])
            ->setEventDate($data['eventDate']->toDateTime())
            ->setLastModified($data['lastModified']->toDateTime())
            ->setItemCount($data['itemCount'])
            ->setStatusCode($data['statusCode'])
            ->setSuccess($data['success']);
    }

}
