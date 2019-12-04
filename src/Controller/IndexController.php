<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use App\Content;

class IndexController
{

    private $content;

    /**
     * @param Content $content [description]
     */
    public function __construct(Content $content)
    {
        $this->content = $content;
    }

    public function index()
    {
        return new JsonResponse(
            $this->content->getContent()
        );
    }
}