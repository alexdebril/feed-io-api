<?php

declare(strict_types=1);

namespace App\Storage\Entity;

use DateTime;
use JsonSerializable;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Serializable;
use MongoDB\BSON\Unserializable;
use MongoDB\BSON\UTCDateTime;
use const DATE_ATOM;

class Result implements Serializable, Unserializable, JsonSerializable
{

    protected ?ObjectId $id;

    protected ObjectId $feedId;

    protected DateTime $eventDate;

    protected DateTime $lastModified;

    protected ?string $error;

    protected bool $success;

    protected int $itemCount = 0;

    protected int $durationInMs = 0;

    protected int $statusCode = 0;

    protected int $minIntervals = 0;

    protected int $medianIntervals = 0;

    protected int $averageIntervals = 0;

    protected int $maxIntervals = 0;

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
     * @return DateTime
     */
    public function getEventDate(): DateTime
    {
        return $this->eventDate;
    }

    /**
     * @param DateTime $eventDate
     * @return Result
     */
    public function setEventDate(DateTime $eventDate): Result
    {
        $this->eventDate = $eventDate;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getLastModified(): DateTime
    {
        return $this->lastModified;
    }

    /**
     * @param DateTime $lastModified
     * @return Result
     */
    public function setLastModified(DateTime $lastModified): Result
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

    /**
     * @return int
     */
    public function getMinIntervals(): int
    {
        return $this->minIntervals;
    }

    /**
     * @param int $minIntervals
     * @return Result
     */
    public function setMinIntervals(int $minIntervals): Result
    {
        $this->minIntervals = $minIntervals;
        return $this;
    }

    /**
     * @return int
     */
    public function getMedianIntervals(): int
    {
        return $this->medianIntervals;
    }

    /**
     * @param int $medianIntervals
     * @return Result
     */
    public function setMedianIntervals(int $medianIntervals): Result
    {
        $this->medianIntervals = $medianIntervals;
        return $this;
    }

    /**
     * @return int
     */
    public function getAverageIntervals(): int
    {
        return $this->averageIntervals;
    }

    /**
     * @param int $averageIntervals
     * @return Result
     */
    public function setAverageIntervals(int $averageIntervals): Result
    {
        $this->averageIntervals = $averageIntervals;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxIntervals(): int
    {
        return $this->maxIntervals;
    }

    /**
     * @param int $maxIntervals
     * @return Result
     */
    public function setMaxIntervals(int $maxIntervals): Result
    {
        $this->maxIntervals = $maxIntervals;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * @param string|null $error
     * @return Result
     */
    public function setError(?string $error): Result
    {
        $this->error = $error;
        return $this;
    }

    public function bsonSerialize(): array
    {
        $properties = get_object_vars($this);
        unset($properties['id']);

        foreach ($properties as $name => $property) {
            if ($property instanceof DateTime) {
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
            ->setMinIntervals(intval($data['minIntervals']))
            ->setMaxIntervals(intval($data['maxIntervals']))
            ->setAverageIntervals(intval($data['averageIntervals']))
            ->setMedianIntervals(intval($data['medianIntervals']))
            ->setSuccess($data['success']);
        if (isset($data['error'])) {
            $this->setError($data['error']);
        }
    }

    public function jsonSerialize(): array
    {
        $out =  [
            'eventDate' => $this->getEventDate()?->format(DATE_ATOM),
            'duration' => $this->getDurationInMs(),
            'statusCode' => $this->getStatusCode(),
            'itemCount' => $this->getItemCount(),
            'success' => $this->isSuccess() ? 'true':'false',
            'lastModified' => $this->getLastModified()?->format(DATE_ATOM),
        ];

        if ($this->isSuccess()) {
            $out['intervals'] = [
                'min' => $this->getMinIntervals(),
                'average' => $this->getAverageIntervals(),
                'median' => $this->getMedianIntervals(),
                'max' => $this->getMaxIntervals(),
            ];
        } else {
            $out['error'] = $this->getError();
        }
        return $out;
    }

}
