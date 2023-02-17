<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\Sortie;
use App\Form\FiltreHomeType;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Services\HomeFiltersCheck;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/', name: 'home')]
class HomeController extends AbstractController
{
    /**
     * @throws \Exception
     */
    #[Route('/', name: '_index')]
    public function index(
        SortieRepository      $sortieRepository,
        ParticipantRepository $participantRepository,
        Request               $request,
        HomeFiltersCheck      $homeFiltersCheck
    ): Response
    {
        //initialisation des filtres "manuels" (hors FiltreHomeType)
        $filtreNom = '';
        $filtreDateMin = '';
        $filtreDateMax = '';
        $filtreDates = false;
        $dateMin = new \DateTime();
        $dateMax = new \DateTime();
        $cocheOrganisateur = true;
        $cocheInscrit = true;
        $cocheNonInscrit = true;
        $cochePassees = false;
        //initialisation du tableau servant à stocker les messages d'anomalie
        $tabErr = ["contenuNom" => '',
                   "datesMinMax" => '' ];
        $yaErreur = false;

        //1.créer une instance de Sortie
        $sortie = new Sortie();
        //initialisation du site avec celui de l'utilisateur connecté
        $utilisateur = $participantRepository->findOneBy(["username" => $this->getUser()->getUserIdentifier()]);
        $siteInit = $utilisateur->getSite();
        $sortie->setSite($siteInit);
        $filtreSite = $siteInit;

        //2.créer une instance du formulaire FiltreHomeType en lien avec l'entité $sortie
        $filtreHomeForm = $this->createForm(FiltreHomeType::class, $sortie);

        //4.traiter le formulaire
        $filtreHomeForm->handleRequest($request);
        if ($filtreHomeForm->isSubmitted() && $filtreHomeForm->isValid()) {

            $filtreSite = $sortie->getSite();
            $filtreNom = htmlspecialchars($request->request->get("filtreNom"));
            $filtreDateMin = $request->request->get("filtreDateMin");
            $filtreDateMax = $request->request->get("filtreDateMax");

            $cocheOrganisateur = ($request->request->get("cocheOrganisateur") !== null);
            $cocheInscrit = ($request->request->get("cocheInscrit") !== null);
            $cocheNonInscrit = ($request->request->get("cocheNonInscrit") !== null);
            $cochePassees = ($request->request->get("cochePassees") !== null);

            //Appel aux méthodes de contrôle des saisies non liées à l'entité Sortie
            $msgErr = $homeFiltersCheck->testDatesMinMax($filtreDateMin, $filtreDateMax);
            if ($msgErr) {
                $tabErr["datesMinMax"] = $msgErr;
            }
            $msgErr = $homeFiltersCheck->testContenuNom($filtreNom);
            if ($msgErr) {
                $tabErr["contenuNom"] = $msgErr;
            }
            foreach ($tabErr as $cle => $valeur) {
                if (strlen($valeur) > 0) {
                    $yaErreur = true;
                }
            }

            //si pas d'erreur de saisie et formulaire validé (pour champ Site)
            if (!$yaErreur ) {
                if (strlen($filtreDateMin) !== 0 && strlen($filtreDateMax) !== 0) {
                    $filtreDates = true;
                    $dateMin = new \DateTime($filtreDateMin);
                    $dateMax = new \DateTime($filtreDateMax);
                }
            }
        }

        $sorties = $sortieRepository->findAllWithFilters($filtreSite,
            $filtreNom, $filtreDates, $dateMin, $dateMax, $utilisateur,
            $cocheOrganisateur, $cocheInscrit, $cocheNonInscrit, $cochePassees);

        //3.envoyer le formulaire au twig
        return $this->render('home/index.html.twig',
            compact('filtreHomeForm', 'tabErr',
                  'filtreNom', 'filtreDateMin', 'filtreDateMax',
                  'cocheOrganisateur', 'cocheInscrit', 'cocheNonInscrit', 'cochePassees',
                  'sorties'));
    }


}


