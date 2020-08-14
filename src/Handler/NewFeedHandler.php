<?php  declare(strict_types=1);


namespace App\Handler;

use App\Message\NewFeed;
use App\Storage\Entity\Feed;
use App\Storage\Repository\FeedRepository;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class NewFeedHandler implements MessageSubscriberInterface
{
    private FeedRepository $repository;

    private SluggerInterface $slugger;

    public function __construct(FeedRepository $repository, SluggerInterface $slugger)
    {
        $this->repository = $repository;
        $this->slugger = $slugger;
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

        $this->repository->save($feed);
    }

}
