<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\FiltreHomeType;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use App\Services\HomeFiltersCheck;
use Exception;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

#[IsGranted('ROLE_USER')]
#[Route('/', name: 'home')]
class HomeController extends AbstractController
{
    /**
     * @param SortieRepository $sortieRepository
     * @param ParticipantRepository $participantRepository
     * @return Response
     * @throws Exception
     */
    #[Route('/', name: '_index')]
    public function index(
        SortieRepository      $sortieRepository,
        ParticipantRepository $participantRepository
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

    /**
     * @param Request $request
     * @param SortieRepository $sortieRepository
     * @param ParticipantRepository $participantRepository
     * @param SiteRepository $siteRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     * @throws Exception
     */
    #[Route('/filtre', name: '_filtre', methods: ['POST'])]
    public function filtre(Request $request, SortieRepository $sortieRepository, ParticipantRepository $participantRepository, SiteRepository $siteRepository, SerializerInterface $serializer): JsonResponse
    {
        $filtre = $request->request->get('filtre');
        $listDetail= [];
        $tabErr = array();

        if(isset($filtre)) {
            $filtreHomeSite = filter_input(INPUT_POST, 'filtre_home_site', FILTER_VALIDATE_INT);
            $filtreNom = htmlspecialchars($request->request->get('filtreNom'));

            $filtreDateMin = strtotime(htmlspecialchars($request->request->get('filtreDateMin')));
            $filtreDateMin = getDate($filtreDateMin);
            $filtreDateMax = strtotime(htmlspecialchars($request->request->get('filtreDateMax')));
            $filtreDateMax = getDate($filtreDateMax);

            $filterCocheOragnisateur = filter_input(INPUT_POST, 'cocheOrganisateur', FILTER_VALIDATE_BOOLEAN);
            $filterCocheInscrit = filter_input(INPUT_POST, 'cocheInscrit', FILTER_VALIDATE_BOOLEAN);
            $filterCocheNonInscrit = filter_input(INPUT_POST, 'cocheNonInscrit', FILTER_VALIDATE_BOOLEAN);
            $filtreCochePassees = filter_input(INPUT_POST, 'cochePassees', FILTER_VALIDATE_BOOLEAN);

            $checkFiltreDates = true;
            $dateMin = new \DateTime();
            $dateMin->setTimestamp($filtreDateMin[0]);
            $dateMin->format('Y-m-d');


            $dateMax = new \DateTime();
            $dateMax->setTimestamp($filtreDateMax[0]);
            $dateMax->format('Y-m-d');

            if(!$filtreHomeSite) {
                $tabErr['site'] = "Le site n'est pas bien renseigné";
            }

            if(preg_match('/^[^@%$?#]+$/i', $filtreNom, $out) === 0 && strlen($filtreNom) > 0) {
                $tabErr['nom'] = 'Les caractères ne doivent pas contenir "@ % $ ? #"';
            }

            //Info: les coche sont générer si contient des filtre (true ou false)
            //Info: si dateMin et Max sont modifier par des string ou int il est générer par défaut à l'état de la date 1970-01-01
            if(($dateMin->format('Y-m-d') === '1970-01-01' && $dateMax->format('Y-m-d') !== '1970-01-01')
                   || ($dateMin->format('Y-m-d') !== '1970-01-01' && $dateMax->format('Y-m-d') === '1970-01-01')) {
                $tabErr['date'] = 'Les 2 bornes de dates doivent être renseignées';
            } else if($dateMin > $dateMax) {
                $tabErr['date'] = "La date minimum ne peut pas être supérieure à la date maximum";
            } else if ($dateMin->format('Y-m-d') === '1970-01-01' && $dateMax->format('Y-m-d') === '1970-01-01') {
                $checkFiltreDates = false;
            }

            if(sizeof($tabErr) > 0) {
                return $this->json(['error' => $tabErr]);
            }

            //initialisation du site avec celui de l'utilisateur connecté
            $utilisateur = $participantRepository->findOneBy(["username" => $this->getUser()->getUserIdentifier()]);
            $filtreSite = $siteRepository->findOneBy(["id" => $filtreHomeSite]);

            $sorties = $sortieRepository->findAllWithFilters($filtreSite,
                $filtreNom, $checkFiltreDates, $dateMin, $dateMax, $utilisateur,
                $filterCocheOragnisateur, $filterCocheInscrit, $filterCocheNonInscrit, $filtreCochePassees);

            foreach ($sorties as $sorty) {
                $context = (new ObjectNormalizerContextBuilder())
                    ->withGroups('show_sortie')
                    ->toArray();

                $temp = new stdClass();
                $temp->countParticipant = count($sorty->getParticipants());
                $temp->isInscrit = 0;
                $temp->action = [];

                foreach ($sorty->getParticipants() as $participant) {
                    if ($participant === $utilisateur) {
                        $temp->isInscrit = 1;
                    }
                }

                if($this->getUser() == $sorty->getOrganisateur() && $sorty->getEtat()->getLibelle() === 'En création') {
                    $temp->action[] = ['path' => '/sortie/', 'name' => 'Modifier'];
                    $temp->action[] = ['path' => '/sortie/publish/', 'name' => 'Publier'];
                } else {
                    $temp->action[] = ['path' => '/sortie/detail/', 'name' => 'Afficher'];

                    if($sorty->getEtat()->getLibelle() === 'Ouvert') {
                        if($temp->isInscrit) {
                            $temp->action[] = ['path' => '/sortie/unsubscribe/', 'name' => 'Se désister'];
                        } else {
                            $temp->action[] = ['path' => '/sortie/subscribe/', 'name' => 'S\'inscrire'];
                        }
                    } else if ($sorty->getEtat()->getLibelle() === 'Clôturé') {
                        if($temp->isInscrit) {
                            $temp->action[] = ['path' => '/sortie/unsubscribe/', 'name' => 'Se désister'];
                        }
                    }
                }

                $json = $serializer->serialize($sorty, 'json', $context);

                $listDetail[] = "[".$json . ", {\"countParticipant\":" . $temp->countParticipant . ", \"isInscrit\": ". $temp->isInscrit . ", \"action\": ". json_encode($temp->action) ."}]";
            }
        }

        return $this->json($listDetail);
    }

}


