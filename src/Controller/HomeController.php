<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\Sortie;
use App\Form\FiltreHomeType;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;

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
        Request               $request
    ): Response
    {
        //initialisation des filtres "manuels" (hors filtreHomeRequest)
        $filtreNom = '';
        $filtreDateMin = '';
        $filtreDateMax = '';
        $cocheOrganisateur = false;
        $cocheInscrit = false;
        $cocheNonInscrit = false;
        $cochePassees = false;

        //1.créer une instance de Sortie
        $sortie = new Sortie();

        //2.créer une instance du formulaire FiltreHomeType en lien avec l'entité $sortie
        $filtreHomeForm = $this->createForm(FiltreHomeType::class, $sortie);

        //4.traiter le formulaire
        $filtreHomeForm->handleRequest($request);
        $sorties = null;
        if ($filtreHomeForm->isSubmitted() && $filtreHomeForm->isValid()) {

            $filtreSite = $sortie->getSite();
            $filtreNom = htmlspecialchars($request->request->get("filtreNom"));
            $filtreDateMin = $request->request->get("filtreDateMin");
            $filtreDateMax = $request->request->get("filtreDateMax");

            $cocheOrganisateur = ($request->request->get("cocheOrganisateur") !== null);
            $cocheInscrit = ($request->request->get("cocheInscrit") !== null);
            $cocheNonInscrit = ($request->request->get("cocheNonInscrit") !== null);
            $cochePassees = ($request->request->get("cochePassees") !== null);

            if (strlen($filtreDateMin) !== 0 && strlen($filtreDateMax) !== 0) {
                $filtreDates = true;
                $dateMin = new \DateTime($filtreDateMin);
                $dateMax = new \DateTime($filtreDateMax);
            }
            else {
                $filtreDates = false;
                $dateMin = new \DateTime();
                $dateMax = new \DateTime();
            }

            $utilisateur = $participantRepository->findOneBy(["username" => $this->getUser()->getUserIdentifier()]);
            $sorties = $sortieRepository->findAllWithFilters($filtreSite,
                $filtreNom, $filtreDates, $dateMin, $dateMax, $utilisateur,
                $cocheOrganisateur, $cocheInscrit, $cocheNonInscrit, $cochePassees);

            //$sorties = $sortieRepository->findBy([], ["dateHeureDeb" => "ASC"], null, 0);
        }

        //3.envoyer le formulaire au twig
        return $this->render('home/index.html.twig',
            compact('filtreHomeForm',
        'filtreNom', 'filtreDateMin', 'filtreDateMax',
                  'cocheOrganisateur', 'cocheInscrit', 'cocheNonInscrit', 'cochePassees',
                  'sorties'));
    }


}


