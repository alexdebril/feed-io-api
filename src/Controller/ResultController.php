<?php declare(strict_types=1);


namespace App\Controller;

use App\Storage\Provider\ResultProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/results', name: 'results_')]
class ResultController
{

    public function __construct(
        private string $allowedOrigin,
    ) {}

    #[Route('/list/{slug}', name: 'list')]
    public function getResults(ResultProvider $provider, string $slug): JsonResponse
    {
        return new JsonResponse(
            ['results' => $provider->getResults($slug)],
            200,
            ['Access-Control-Allow-Origin' => $this->allowedOrigin]
        );
    }

    #[Route('/stats/{slug}/{days}', name: 'stats')]
    public function getStats(ResultProvider $provider, string $slug, int $days = 1): JsonResponse
    {
        return new JsonResponse(
            $provider->getStats($slug, $days),
            200,
            ['Access-Control-Allow-Origin' => $this->allowedOrigin]
        );
    }
}
