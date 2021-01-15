<?php

namespace App\Controller;

use App\Content;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class IndexController
{

    public function __construct(
        private Content $content
    ) {}

    #[Route('/', name: 'index')]
    public function index()
    {
        return new JsonResponse(
            $this->content->getContent()
        );
    }
}
