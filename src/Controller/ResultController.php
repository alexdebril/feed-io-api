<?php declare(strict_types=1);


namespace App\Controller;

use App\Storage\Provider\ResultProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/result', name: 'result_')]
class ResultController
{

    #[Route('/list/{slug}', name: 'list')]
    public function getResults(ResultProvider $provider, string $slug): JsonResponse
    {
        return new JsonResponse(
            $provider->getResults($slug),
            200
        );
    }

    #[Route('/stats/{slug}/{days}', name: 'stats')]
    public function getStats(ResultProvider $provider, string $slug, int $days = 1): JsonResponse
    {
        return new JsonResponse(
            $provider->getStats($slug, $days),
            200
        );
    }
}
