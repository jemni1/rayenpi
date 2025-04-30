<?php

// src/Service/RouteService.php
namespace App\Service;

use GuzzleHttp\Client;

class RouteService
{
    private $client;
    private $apiKey;

    public function __construct(Client $client, string $apiKey)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    /**
     * Calcule l'itinéraire entre un point de départ et un point d'arrivée via OpenRouteService.
     *
     * @param string $startLat Latitude du point de départ
     * @param string $startLng Longitude du point de départ
     * @param string $endLat Latitude du point d'arrivée
     * @param string $endLng Longitude du point d'arrivée
     * @param string $mode Mode de transport (driving, cycling, walking)
     *
     * @return array
     */
    public function calculateRoute(string $startLat, string $startLng, string $endLat, string $endLng, string $mode = 'driving'): array
    {
        $url = "https://api.openrouteservice.org/v2/directions/$mode";

        // Corps de la requête
        $body = [
            'coordinates' => [
                [$startLng, $startLat],  // Départ
                [$endLng, $endLat]       // Arrivée
            ]
        ];

        // Envoi de la requête à l'API
        $response = $this->client->post($url, [
            'json' => $body,
            'headers' => [
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        // Vérification de la réponse et extraction des informations
        if ($data && isset($data['routes'][0])) {
            $route = $data['routes'][0];
            $duration = $route['summary']['duration'] / 60;  // Conversion en minutes
            $distance = $route['summary']['distance'] / 1000; // Conversion en kilomètres

            return [
                'distance' => number_format($distance, 2) . ' km',
                'duration' => number_format($duration, 2) . ' minutes',
                'steps' => $route['segments'][0]['steps']
            ];
        }

        // Retour d'une erreur si l'itinéraire n'a pas pu être calculé
        return ['error' => 'Impossible de calculer l\'itinéraire.'];
    }
}
