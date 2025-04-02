<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('fr_date', [$this, 'formatDateFr']),
            new TwigFilter('fr_jour_nom', [$this, 'formatJourNomFr']),
            new TwigFilter('fr_jour_mois', [$this, 'formatJourMoisFr']),
            new TwigFilter('fr_wind_dir', [$this, 'traduireDirectionVent']),
            new TwigFilter('wind_abbr_fr', [$this, 'traduireAbreviationFr']),
            new TwigFilter('nettoie_condition', [$this, 'nettoyerCondition']),
        ];
    }

    public function formatDateFr($date): string
    {
        if (!$date instanceof \DateTimeInterface) {
            $date = new \DateTime($date);
        }

        $jours = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
        $mois = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin',
                 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

        $jourSemaine = $jours[(int)$date->format('w')];
        $jour = $date->format('d');
        $moisNom = $mois[(int)$date->format('n') - 1];

        return ucfirst($jourSemaine) . ' ' . $jour . ' ' . $moisNom;
    }

    public function formatJourNomFr($date): string
    {
        if (!$date instanceof \DateTimeInterface) {
            $date = new \DateTime($date);
        }

        $jours = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
        return ucfirst($jours[(int)$date->format('w')]);
    }

    public function formatJourMoisFr($date): string
    {
        if (!$date instanceof \DateTimeInterface) {
            $date = new \DateTime($date);
        }

        $mois = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin',
                 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

        $jour = $date->format('d');
        $moisNom = $mois[(int)$date->format('n') - 1];

        return $jour . ' ' . $moisNom;
    }

    public function traduireDirectionVent(string $code): string
    {
        $directions = [
            'N' => 'Nord', 'NNE' => 'Nord-Nord-Est', 'NE' => 'Nord-Est', 'ENE' => 'Est-Nord-Est',
            'E' => 'Est', 'ESE' => 'Est-Sud-Est', 'SE' => 'Sud-Est', 'SSE' => 'Sud-Sud-Est',
            'S' => 'Sud', 'SSW' => 'Sud-Sud-Ouest', 'SW' => 'Sud-Ouest', 'WSW' => 'Ouest-Sud-Ouest',
            'W' => 'Ouest', 'WNW' => 'Ouest-Nord-Ouest', 'NW' => 'Nord-Ouest', 'NNW' => 'Nord-Nord-Ouest',
            'N/A' => 'N/A'
        ];

        $code = strtoupper(trim($code));
        return $directions[$code] ?? $code;
    }

    public function traduireAbreviationFr(string $code): string
    {
        $abbrs = [
            'N' => 'N', 'NNE' => 'N/NE', 'NE' => 'NE', 'ENE' => 'E/NE',
            'E' => 'E', 'ESE' => 'E/SE', 'SE' => 'SE', 'SSE' => 'S/SE',
            'S' => 'S', 'SSW' => 'S/SO', 'SW' => 'SO', 'WSW' => 'O/SO',
            'W' => 'O', 'WNW' => 'O/NO', 'NW' => 'NO', 'NNW' => 'N/NO',
            'N/A' => 'N/A'
        ];

        $code = strtoupper(trim($code));
        return $abbrs[$code] ?? $code;
    }
    public function nettoyerCondition(string $condition): string
{
    return str_ireplace('à proximité', '', $condition); // retire juste "à proximité"
}

}
