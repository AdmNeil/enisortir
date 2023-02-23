<?php

namespace App\Services;

use App\Entity\Sortie;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailService
{
    private $mailer;

    public function __construct (
        MailerInterface $mailer)  //injection d'un service Symfony
    {
        $this->mailer = $mailer;
    }

    public function sendMailCancel(string $expediteur,
                                   string $destinataire,
                                   Sortie $sortie) {
        $email = (new Email())
            ->from($expediteur)
            ->to($destinataire)
            ->subject('Annulation de la sortie '.$sortie->getNom())
            ->text('Bonjour, je suis au regret de vous annoncer que je suis dans l\'obligation d\'annuler la sortie '.$sortie->getNom().' prévue le '.$sortie->getDateHeureDeb()->format("d/m/y").', pour le motif suivant : ' .$sortie->getInfosSortie() .'.')
        ;

        $this->mailer->send($email);
    }
}