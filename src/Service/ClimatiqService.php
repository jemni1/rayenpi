<?php
// src/Service/ClimatiqService.php
namespace App\Service;

use GuzzleHttp\Client;

class ClimatiqService
{
    private $client;
    private $apiKey;

    public function __construct(Client $client, string $apiKey)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    public function calculateCarbonFootprint($transport, $distance)
    {
        try {
            $response = $this->client->request('POST', 'https://beta3.api.climatiq.io/travel/' . $transport, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'distance' => $distance,
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);  // Ajout d'une méthode de décodage explicite

            // Affichage des logs pour le débogage
            //dump($data); die;

            return $data['data']['carbon_saved'] ?? null; // Retourne la quantité de CO2 économisée
        } catch (\Exception $e) {
            return null;
        }
    }
}
