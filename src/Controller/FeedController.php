<?php

namespace App\Controller;

use \FeedIo\FeedIo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FeedController
{
    /**
     * @var \FeedIo\FeedIo
     */
    private $feedIo;

    public function __construct(FeedIo $feedIo)
    {
        $this->feedIo = $feedIo;
    }

    public function consume(Request $request)
    {
        $result = $this->feedIo->read($request->get('url'));
        return new Response($this->feedIo->format($result->getFeed(), 'json'));
    }
}
