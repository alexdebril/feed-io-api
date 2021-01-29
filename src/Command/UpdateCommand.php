<?php

declare(strict_types=1);

namespace App\Command;

use App\Storage\Entity\Feed;
use App\Storage\Entity\Item;
use App\Storage\Repository\FeedRepository;
use App\Storage\Repository\ItemRepository;
use App\Storage\Repository\ResultRepository;
use FeedIo\Adapter\HttpRequestException;
use FeedIo\Adapter\NotFoundException;
use FeedIo\Adapter\ServerErrorException;
use FeedIo\FeedIo;
use FeedIo\Reader\ReadErrorException;
use FeedIo\Reader\Result;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command
{
    const WAIT = 60;

    const DEFAULT_BATCH_LIMIT = 16;

    private LoggerInterface $logger;

    private FeedIo $feedIo;

    private FeedRepository $feedRepository;

    private ItemRepository $itemRepository;

    private ResultRepository $resultRepository;

    private int $batchCount = 1;

    public function __construct(
        LoggerInterface $logger,
        FeedIo $feedIo,
        FeedRepository $feedRepository,
        ItemRepository $itemRepository,
        ResultRepository $resultRepository
    ) {
        $this->logger = $logger;
        $this->feedIo = $feedIo;
        $this->feedRepository = $feedRepository;
        $this->itemRepository = $itemRepository;
        $this->resultRepository = $resultRepository;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:update')
            ->setDescription('update feeds.')
            ->addOption(
                'iterations', 'i',
                InputOption::VALUE_OPTIONAL,
                'number of iterations before leaving', self::DEFAULT_BATCH_LIMIT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger->info('starting updater', [
            'iterations' => $input->getOption('iterations'),
        ]);
        do {
            try {
                $this->logger->info('updating feeds', ['batch' => $this->batchCount]);
                $feeds = $this->feedRepository->getFeedsToUpdate();
                foreach ($feeds as $feed) {
                    $this->updateFeed($feed);
                }
            } catch (\Throwable $e) {
                $this->logger->error('error updating feeds', [
                    'error' => $e->getMessage(),
                    'batch' => $this->batchCount,
                ]);
                $this->logger->debug('error updating feeds', [
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        } while ($this->keepRunning(intval($input->getOption('iterations'))));

        return 0;
    }

    protected function updateFeed(Feed $feed): void
    {
        try {
            $this->logger->info('updating', ['batch' => $this->batchCount, 'feed' => $feed->getSlug()]);
            $result = $this->feedIo->read($feed->getUrl(), $feed, $feed->getLastModified());
            $this->logger->debug('result fetched', [
                'batch' => $this->batchCount,
                'feed' => $feed->getSlug(),
                'last-modified' => $feed->getLastModified(),
            ]);
            $this->saveResult($this->newSuccessResult($result, $feed));

            if (count($result->getFeed()) > 0) {
                foreach ($result->getFeed() as $item) {
                    $this->saveItem($feed, $item);
                }
                $this->logger->info('items fetched', ['batch' => $this->batchCount, 'feed' => $feed->getSlug()]);
                $feed->setResult($result);
                $this->feedRepository->save($feed);
            }
        } catch (\Exception $e) {
            $this->saveResult($this->newFailureResult($e, $feed));
            $this->logger->error('feed not updated', [
                'error' => $e->getMessage(),
                'batch' => $this->batchCount,
                'feed' => $feed->getSlug(),
            ]);
            $this->logger->debug('feed not updated', [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    protected function newSuccessResult(Result $result, Feed $feed): \App\Storage\Entity\Result
    {
        $res = new \App\Storage\Entity\Result();
        $res
            ->setStatusCode($result->getResponse()->getStatusCode())
            ->setDurationInMs($result->getResponse()->getDuration())
            ->setMinIntervals($result->getUpdateStats()->getMinInterval())
            ->setMedianIntervals($result->getUpdateStats()->getMedianInterval())
            ->setAverageIntervals($result->getUpdateStats()->getAverageInterval())
            ->setMaxIntervals($result->getUpdateStats()->getMaxInterval())
            ->setSuccess(true)
            ->setItemCount(count($feed))
            ->setLastModified($feed->getLastModified())
            ->setEventDate(new \DateTime())
            ->setFeedId($feed->getId());

        return $res;
    }

    protected function newFailureResult(\Exception $exception, Feed $feed): \App\Storage\Entity\Result
    {
        $rootException = $exception->getPrevious();
        $res = new \App\Storage\Entity\Result();
        $res
            ->setStatusCode($rootException instanceof ServerErrorException ? $rootException->getResponse()->getStatusCode():200)
            ->setDurationInMs($rootException instanceof HttpRequestException ? $rootException->getDuration():0)
            ->setSuccess(false)
            ->setItemCount(0)
            ->setError($exception->getMessage())
            ->setLastModified($feed->getLastModified())
            ->setEventDate(new \DateTime())
            ->setFeedId($feed->getId());

        return $res;
    }

    protected function saveResult(\App\Storage\Entity\Result $result): void
    {;
        $this->resultRepository->save($result);
    }

    protected function saveItem(Feed $feed, Item $item): void
    {
        try {
            $this->logger->info('saving item', ['batch' => $this->batchCount, 'feed' => $feed->getSlug(), 'item' => $item->getLink()]);
            $item->setFeedId($feed->getId());
            $item->setLanguage($feed->getLanguage());
            $this->itemRepository->save($item);
        } catch (\Exception $e) {
            $this->logger->warning('error saving item', [
                'error' => $e->getMessage(),
                'batch' => $this->batchCount,
                'feed' => $feed->getSlug(),
            ]);
            $this->logger->debug('error saving item', [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    protected function keepRunning(int $iterations): bool
    {
        if ($this->batchCount > $iterations) {
            $this->logger->notice("iteration limit reached ({$iterations}), stopping", ['batch' => $this->batchCount]);

            return false;
        }

        $this->logger->info('finished, waiting', ['batch' => $this->batchCount]);
        ++$this->batchCount;
        sleep(self::WAIT);

        return true;
    }
}
