<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MeteoController extends AbstractController
{
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        $this->apiKey = $_ENV['WEATHERAPI_KEY'];
    }

    #[Route('/', name: 'home')]
    public function home(): Response
    {
        return $this->redirectToRoute('meteo_index');
    }

    #[Route('/meteo', name: 'meteo_index')]
    public function index(Request $request): Response
    {
        $ville = $request->query->get('ville', 'Paris');
        $meteoActuelle = null;
        $previsions = [];
        $heuresParJour = [];

        setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'fr');

        try {
            // Météo actuelle
            $responseActuelle = $this->client->request('GET', 'http://api.weatherapi.com/v1/current.json', [
                'query' => [
                    'key' => $this->apiKey,
                    'q' => $ville,
                    'lang' => 'fr'
                ]
            ]);
            $meteoActuelle = $responseActuelle->toArray();

            // Prévisions sur 3 jours
            $responsePrevisions = $this->client->request('GET', 'http://api.weatherapi.com/v1/forecast.json', [
                'query' => [
                    'key' => $this->apiKey,
                    'q' => $ville,
                    'days' => 3,
                    'lang' => 'fr'
                ]
            ]);

            $dataPrevisions = $responsePrevisions->toArray();
            foreach ($dataPrevisions['forecast']['forecastday'] as $jour) {
                $date = new \DateTime($jour['date']);
            
                $previsions[] = [
                    'date' => $date,
                    'nom' => ucfirst((new \IntlDateFormatter('fr_FR', \IntlDateFormatter::FULL, \IntlDateFormatter::NONE))->format($date)),
                    'max' => $jour['day']['maxtemp_c'],
                    'min' => $jour['day']['mintemp_c'],
                    'condition' => $jour['day']['condition']['text'],
                    'icon' => basename($jour['day']['condition']['icon']),
                    'vent_vitesse' => round($jour['day']['maxwind_kph']),
                    'vent_direction' => strtoupper(trim($jour['day']['maxwind_dir'] ?? 'N/A')), // ✅ CORRECTION ICI
                    'precipitation' => $jour['day']['daily_chance_of_rain'],
                    'mm' => $jour['day']['totalprecip_mm']
                ];
            
                $heuresParJour[$jour['date']] = [];
            
                foreach ($jour['hour'] as $heure) {
                    $heuresParJour[$jour['date']][] = [
                        'time' => $heure['time'],
                        'temp' => $heure['temp_c'],
                        'feelslike_c' => $heure['feelslike_c'],
                        'icon' => $heure['condition']['icon'],
                        'text' => $heure['condition']['text'],
                        'wind_kph' => $heure['wind_kph'],
                        'wind_dir' => $heure['wind_dir'],
                        'chance_of_rain' => $heure['chance_of_rain'],
                        'precip_mm' => $heure['precip_mm'],
                        'humidity' => $heure['humidity'] ?? null 
                    ];
                }
            }
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur avec l’API ou ville non trouvée.');
        }

        // Jour actuel
        // Jour actuel
        $jourActuel = (new \DateTime())->format('Y-m-d');
        $formatterJour = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::FULL, \IntlDateFormatter::NONE);
        $formatterJour->setPattern("EEEE d MMMM"); // Ex: Mardi 1 avril
        $jourActuelLabel = ucfirst($formatterJour->format(new \DateTime()));      

        // Données pour le carrousel météo
        $villesCarousel = ['Paris', 'Niort', 'Montpellier'];
        $carouselData = $this->getMeteoVilles($villesCarousel);

        return $this->render('meteo.html.twig', [
            'ville' => $ville,
            'meteo' => $meteoActuelle,
            'previsions' => $previsions,
            'heuresParJour' => $heuresParJour,
            'jourActuel' => $jourActuel,
            'jourActuelLabel' => $jourActuelLabel,
            'villesCarrousel' => $carouselData
        ]);
    }

    private function getMeteoVilles(array $villes): array
    {
        $resultats = [];

        $departements = [
            'Paris' => 'Paris',
            'Niort' => 'Deux-Sèvres',
            'Montpellier' => 'Hérault'
        ];

        foreach ($villes as $ville) {
            try {
                $response = $this->client->request('GET', 'http://api.weatherapi.com/v1/current.json', [
                    'query' => [
                        'key' => $this->apiKey,
                        'q' => $ville,
                        'lang' => 'fr'
                    ]
                ]);

                $data = $response->toArray();

                $resultats[] = [
                    'ville' => $data['location']['name'],
                    'departement' => $departements[$data['location']['name']] ?? $data['location']['region'],
                    'icon' => $data['current']['condition']['icon'],
                    'condition' => $data['current']['condition']['text'],
                    'temp' => round($data['current']['temp_c'])
                ];
            } catch (\Exception $e) {
                continue;
            }
        }

        return $resultats;
    }
}
