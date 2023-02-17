<?php

namespace App\Services;

class HomeFiltersCheck
{
    public function testDatesMinMax($filtreDateMin, $filtreDateMax): string
    {
        $msg = '';
        //si une seule des dates est renseignée => msg erreur
        if ((strlen($filtreDateMin) == 0 && strlen($filtreDateMax) !== 0)
            || (strlen($filtreDateMin) !== 0 && strlen($filtreDateMax) == 0)) {
            $msg = 'Les 2 bornes de dates doivent être renseignées.';
        } else if (strlen($filtreDateMin) !== 0 && strlen($filtreDateMax) !== 0) {
            //si les 2 dates sont renseignées
            //et min sup à max => msg erreur
            if ($filtreDateMax < $filtreDateMin) {
                $msg = 'Les bornes de date ne sont pas valides.';
            } else {
                //et antérieures à un mois => msg erreur
                $dateJourStr = (new \Datetime())->format('Y-m-d H:i:s');
                $dateMinSortieStr = date("Y-m-d H:i:s", strtotime($dateJourStr) - (30 * 24 * 60 * 60));
                if (($filtreDateMin < $dateMinSortieStr) && ($filtreDateMax < $dateMinSortieStr)) {
                    $msg = 'Les sorties antérieures à un mois ne sont plus consultables.';
                }
            }
        }
        return $msg;
    }
    public function testContenuNom($filtreNom): string
    {
        $msg = '';
        if (strlen($filtreNom) > 30) {
            $msg = 'Ce champ ne peut pas dépasser 30 caractères.';
        }
        return $msg;
    }
}