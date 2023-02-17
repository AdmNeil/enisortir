<?php

namespace App\Services;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Repository\ParticipantRepository;


class SortieUpdateCheck
{

    public function dejaInscrit(Participant           $utilisateur,
                                Sortie                $sortie): bool
    {
        //Vérification que l'utilisateur connecté est bien déjà inscrit
        $dejaInscrit = false;
        foreach ($sortie->getParticipants() as $participant) {
            $dejaInscrit = ($participant === $utilisateur);
        }
        return $dejaInscrit;
    }
}