<?php

 declare(strict_types=1);

namespace App\Handler;

use App\Message\NewFeed;
use App\Storage\Entity\Feed;
use App\Storage\Repository\FeedRepository;
use FeedIo\Check\CheckAvailability;
use FeedIo\Check\CheckLastModified;
use FeedIo\Check\CheckPublicIds;
use FeedIo\Check\CheckReadSince;
use FeedIo\Check\Processor;
use FeedIo\FeedIo;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class NewFeedHandler implements MessageSubscriberInterface
{

    private FeedIo $feedIo;

    private FeedRepository $repository;

    private SluggerInterface $slugger;

    private Processor $processor;

    public function __construct(FeedIo $feedIo, FeedRepository $repository, SluggerInterface $slugger)
    {
        $this->feedIo = $feedIo;
        $this->repository = $repository;
        $this->slugger = $slugger;
        $this->processor = new Processor($feedIo);
        $this->processor
            ->add(new CheckAvailability())
            ->add(new CheckPublicIds())
            ->add(new CheckLastModified())
            ->add(new CheckReadSince());
    }

    public static function getHandledMessages(): iterable
    {
        yield NewFeed::class;
    }

    public function __invoke(NewFeed $newFeed)
    {
        $feed = new Feed();
        $feed->setUrl($newFeed->getUrl());
        $slug = $this->slugger->slug(str_replace(['https://', 'http://'], '', $newFeed->getUrl()));
        $feed->setSlug($slug->toString());

        $this->checkFeed($feed);
        $this->repository->save($feed);
    }

    private function checkFeed(Feed $feed)
    {
        $result = $this->processor->run($feed->getUrl());
        $checks = [];
        list($u,
            $checks['accessible'],
            $checks['updateable'],
            $checks['modifiedSince'],
            $checks['items'],
            $checks['uniqueIds'],
            $checks['dateFlow']) = $result->toArray();
        $feed->setChecks($checks);
    }
}
