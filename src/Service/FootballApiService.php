<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class FootballApiService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $footballApiUrl,
        private readonly string $footballApiKey,
    ) {
    }

    /**
     * Récupère la liste des équipes d'une compétition donnée.
     * Exemple de code compétition : WC (World Cup), EC (Euro), CL (Champions League)
     */
    public function getEquipesCompetition(string $codeCompetition): array
    {
        $response = $this->httpClient->request('GET', $this->footballApiUrl . '/competitions/' . $codeCompetition . '/teams', [
            'headers' => [
                'X-Auth-Token' => $this->footballApiKey,
            ],
        ]);

        $data = $response->toArray();

        return $data['teams'] ?? [];
    }
}