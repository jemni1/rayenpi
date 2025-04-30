<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CarbonFootprintController extends AbstractController
{
    private $httpClient;
    private $carbonInterfaceApiKey;

    // Facteurs d'émission par défaut (en kg CO2/km) lorsque l'API échoue
    private const EMISSION_FACTORS = [
        'car-petrol' => 0.192,
        'car-diesel' => 0.171,
        'car-hybrid' => 0.112,
        'car-electric' => 0.053,
        'bus' => 0.089,
        'train' => 0.041,
        'bike' => 0,
        'motorcycle' => 0.113,
        'walk' => 0,
        'shuttle' => 0.089,
    ];

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->carbonInterfaceApiKey = $_ENV['CARBON_INTERFACE_API_KEY'] ?? 'dfoRv4e2pwIS2yIjQSeUwQ';
    }

    #[Route('/carbon-footprint', name: 'carbon_footprint')]
    public function index(): Response
    {
        $transportModes = [
            'car-petrol' => 'Voiture (essence)',
            'car-diesel' => 'Voiture (diesel)',
            'car-hybrid' => 'Voiture (hybride)',
            'car-electric' => 'Voiture (électrique)',
            'bus' => 'Bus',
            'train' => 'Train',
            'bike' => 'Vélo',
            'motorcycle' => 'Moto',
            'walk' => 'Marche',
            'shuttle' => 'Navette',
        ];

        return $this->render('carbon_footprint/index.html.twig', [
            'transportModes' => $transportModes,
        ]);
    }

    #[Route('/carbon-footprint/calculate', name: 'carbon_footprint_calculate', methods: ['POST'])]
    public function calculate(Request $request): Response
    {
        $transportMode = $request->request->get('transport_mode');
        $distance = (float) $request->request->get('distance');

        if (!$transportMode || $distance <= 0) {
            $this->addFlash('error', 'Veuillez sélectionner un mode de transport et entrer une distance valide.');
            return $this->redirectToRoute('carbon_footprint');
        }

        try {
            $selectedEmission = $this->calculateEmissions($transportMode, $distance);
            $carEmission = $this->calculateEmissions('car-petrol', $distance);

            $savedEmissions = 0;
            if (!str_starts_with($transportMode, 'car-')) {
                $savedEmissions = $carEmission - $selectedEmission;
            }

            return $this->render('carbon_footprint/result.html.twig', [
                'transportMode' => $transportMode,
                'distance' => $distance,
                'emission' => $selectedEmission,
                'carEmission' => $carEmission,
                'savedEmissions' => $savedEmissions,
                'transportModeName' => $this->getTransportModeName($transportMode),
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur s\'est produite lors du calcul des émissions. Les valeurs par défaut ont été utilisées.');
            
            // Utilisation des facteurs d'émission par défaut en cas d'erreur
            $selectedEmission = $distance * (self::EMISSION_FACTORS[$transportMode] ?? 0);
            $carEmission = $distance * self::EMISSION_FACTORS['car-petrol'];
            $savedEmissions = str_starts_with($transportMode, 'car-') ? 0 : $carEmission - $selectedEmission;

            return $this->render('carbon_footprint/result.html.twig', [
                'transportMode' => $transportMode,
                'distance' => $distance,
                'emission' => $selectedEmission,
                'carEmission' => $carEmission,
                'savedEmissions' => $savedEmissions,
                'transportModeName' => $this->getTransportModeName($transportMode),
                'usedDefault' => true,
            ]);
        }
    }

    private function calculateEmissions(string $transportMode, float $distance): float
    {
        // Modes sans émissions
        if (in_array($transportMode, ['bike', 'walk'])) {
            return 0.0;
        }

        // Configuration des types de véhicules
        $vehicleTypes = [
            'car-petrol' => ['type' => 'vehicle', 'distance_unit' => 'km', 'distance_value' => $distance, 'vehicle_model_id' => '7268a4b7-17e8-4c54-8422-006845f5f132'],
            'car-diesel' => ['type' => 'vehicle', 'distance_unit' => 'km', 'distance_value' => $distance, 'vehicle_model_id' => '7268a4b7-17e8-4c54-8422-006845f5f133'],
            'car-hybrid' => ['type' => 'vehicle', 'distance_unit' => 'km', 'distance_value' => $distance, 'vehicle_model_id' => '7268a4b7-17e8-4c54-8422-006845f5f134'],
            'car-electric' => ['type' => 'vehicle', 'distance_unit' => 'km', 'distance_value' => $distance, 'vehicle_model_id' => '7268a4b7-17e8-4c54-8422-006845f5f135'],
            'bus' => ['type' => 'vehicle', 'distance_unit' => 'km', 'distance_value' => $distance, 'vehicle_model_id' => '7229a4b7-17e8-4c54-8422-006845f5f132'],
            'train' => ['type' => 'vehicle', 'distance_unit' => 'km', 'distance_value' => $distance, 'vehicle_model_id' => '7229a4b7-17e8-4c54-8422-006845f5f133'],
            'motorcycle' => ['type' => 'vehicle', 'distance_unit' => 'km', 'distance_value' => $distance, 'vehicle_model_id' => '7229a4b7-17e8-4c54-8422-006845f5f134'],
            'shuttle' => ['type' => 'vehicle', 'distance_unit' => 'km', 'distance_value' => $distance, 'vehicle_model_id' => '7229a4b7-17e8-4c54-8422-006845f5f132'],
        ];

        try {
            $response = $this->httpClient->request('POST', 'https://www.carboninterface.com/api/v1/estimates', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->carbonInterfaceApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $vehicleTypes[$transportMode] ?? $vehicleTypes['car-petrol'],
            ]);

            $data = $response->toArray();

            if (isset($data['data']['attributes']['carbon_kg'])) {
                return (float) $data['data']['attributes']['carbon_kg'];
            }

            throw new \Exception('Réponse API incomplète');
        } catch (\Exception $e) {
            // En cas d'erreur, utiliser les valeurs par défaut
            return $distance * (self::EMISSION_FACTORS[$transportMode] ?? 0);
        }
    }

    private function getTransportModeName(string $transportMode): string
    {
        $names = [
            'car-petrol' => 'Voiture (essence)',
            'car-diesel' => 'Voiture (diesel)',
            'car-hybrid' => 'Voiture (hybride)',
            'car-electric' => 'Voiture (électrique)',
            'bus' => 'Bus',
            'train' => 'Train',
            'bike' => 'Vélo',
            'motorcycle' => 'Moto',
            'walk' => 'Marche',
            'shuttle' => 'Navette',
        ];

        return $names[$transportMode] ?? $transportMode;
    }
}