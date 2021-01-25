<?php declare(strict_types=1);


namespace App\Controller;

use App\Storage\Provider\ResultProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/result', name: 'result_')]
class ResultController
{

    #[Route('/list/{slug}', name: 'list')]
    public function getResults(string $slug, ResultProvider $provider): JsonResponse
    {
        return new JsonResponse(
            $provider->getResults($slug),
            200
        );
    }

    #[Route('/stats/{slug}', name: 'stats')]
    public function getStats(string $slug, ResultProvider $provider): JsonResponse
    {
        return new JsonResponse(
            $provider->getStats($slug),
            200
        );
    }
}
