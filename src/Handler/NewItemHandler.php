<?php declare(strict_types=1);


namespace App\Handler;


use App\Storage\Entity\Feed;
use App\Storage\Entity\Item;
use GuzzleHttp\Client;

class NewItemHandler
{

    public function __construct(private Client $client, private string $host)
    {}

    public function notify(Feed $feed, Item $item): void
    {
        $payload = [
            'title' => $item->getTitle(),
            'feed_url' => $feed->getUrl(),
        ];
        $this->client->post($this->host, [
            'body' => json_encode($payload)
        ]);
    }

}
