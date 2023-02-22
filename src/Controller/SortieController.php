<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\LieuType;
use App\Form\SortieType;
use App\Form\VilleType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use DateTime;
use App\Services\SortieUpdateCheck;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_USER')]
#[Route('/sortie', name: 'sortie')]
class SortieController extends AbstractController
{
    /**
     * @throws NonUniqueResultException
     */
    #[Route('/@new', name: '_index')]
    public function index(EntityManagerInterface $em, Request $request, EtatRepository $etatRepository, VilleRepository $villeRepository, ParticipantRepository $participantRepository): Response
    {
        $user = $participantRepository->findOneBy(['username' => $this->getUser()->getUserIdentifier()]);

        $sortie = new Sortie();
        $lieu = new Lieu();
        $ville = new Ville();

        $sortie->setSite($user->getSite());
        $sortie->setOrganisateur($this->getUser());

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $lieuForm = $this->createForm(LieuType::class, $lieu);
        $villeForm = $this->createForm(VilleType::class, $ville);

        $sortieForm->handleRequest($request);
        $lieuForm->handleRequest($request);
        $villeForm->handleRequest($request);

        $VilleExist = $villeRepository->findIfExistVille($villeForm->get('codePostal')->getData());

        if (sizeof($VilleExist) === 1) {
            $lieu->setVille($VilleExist[0]);
        } else {
            $lieu->setVille($villeForm->getData());
        }
        $sortie->setLieu($lieuForm->getData());

        //contrôle des Asserts des entités Lieu et Ville
        $lieuOk = $this->testAsserts($sortieForm->getData()->getLieu());

        if ($sortieForm->isSubmitted() && $sortieForm->isValid() && $lieuOk) {

            $intEtat = null;

            if ($sortieForm->getClickedButton()->getName() === "saveSortie") {
                $intEtat = 1;
                $this->addFlash('success', 'Sortie enregistrée');
            } else if ($sortieForm->getClickedButton()->getName() === "publishSortie") {
                $intEtat = 2;
                $this->addFlash('success', 'Sortie publiée');
            }

            $etatFind = $etatRepository->findOneBy(['id' => $intEtat]);
            $sortie->setEtat($etatFind);

            if (sizeof($VilleExist) === 0) $em->persist($ville);

            $em->persist($lieu);
            $em->persist($sortie);
            $em->flush();

            return $this->redirectToRoute('home_index');
        }

        return $this->render('sortie/index.html.twig', compact('sortieForm', 'lieuForm', 'villeForm'));
    }

    /** Fonction contrôlant les Asserts des entités Lieu et Ville
     *  car non gérés automatiquement par le formulaire $sortieForm
     *  (de part la façon dont il a été "construit")
     * @param Lieu $lieu
     * @return bool
     */
    public function testAsserts(Lieu $lieu): bool {

        $saisieValide = true;
        if (strlen($lieu->getVille()->getNom()) < 3 || strlen($lieu->getVille()->getNom()) > 30
            || !preg_match("/^[0-9]{4,5}+$/", $lieu->getVille()->getCodePostal())
            || strlen($lieu->getNom()) < 3 || strlen($lieu->getNom()) > 30
            || strlen($lieu->getRue()) < 3 || strlen($lieu->getRue()) > 30
            ) {
            $saisieValide = false;
        }
        return $saisieValide;
    }

    /*#[Route('/{id}', name: '_update', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function update(int $id, EntityManagerInterface $em, Request $request, EtatRepository $etatRepository, SortieRepository $sortieRepository, LieuRepository $lieuRepository, VilleRepository $villeRepository, ParticipantRepository $participantRepository): Response
    {

    }*/

    /**
     * @throws NonUniqueResultException
     * @throws \Exception
     */
    #[Route('/{id}', name: '_update', requirements: ['id' => '\d+'])]
    public function update(int $id, EntityManagerInterface $em, Request $request, EtatRepository $etatRepository, SortieRepository $sortieRepository, LieuRepository $lieuRepository, VilleRepository $villeRepository, ParticipantRepository $participantRepository): Response
    {
        $user = $participantRepository->findOneBy(['username' => $this->getUser()->getUserIdentifier()]);
        $findMySortie = $sortieRepository->findOneBy(['organisateur' => $user, 'id' => $id]);

        if ($findMySortie === null) {
            $this->addFlash('error', "Vous n'êtes pas l'organisateur de cette sortie");
            return $this->redirectToRoute('home_index');
        }

        $findMyEtat = $etatRepository->findOneBy(['id' => $findMySortie->getEtat()->getId()]);

        $datecloture = new DateTime($findMySortie->getDateCloture()->format('Y-m-d h:i'));
        $dateStartEvent = new DateTime($findMySortie->getDateHeureDeb()->format('Y-m-d h:i'));

        if(preg_match("/^[^1]$/i", $findMyEtat->getId()) || $datecloture >= $dateStartEvent ) {
            $this->addFlash('error', "L'état du formulaire Sortie ne peut être modifié ou annulé");
            return $this->redirectToRoute('home_index');
        }

        $findMyLieu = $lieuRepository->findOneBy(['id' => $findMySortie->getLieu()->getId()]);
        $findMyVille = $villeRepository->findOneBy(['id' => $findMyLieu->getVille()->getId()]);

        $sortieForm = $this->createForm(SortieType::class, $findMySortie);
        $lieuForm = $this->createForm(LieuType::class, $findMyLieu);
        $villeForm = $this->createForm(VilleType::class, $findMyVille);

        $sortieForm->handleRequest($request);
        $lieuForm->handleRequest($request);
        $villeForm->handleRequest($request);

        $lieuForm->getData()->setVille($villeForm->getData());

        $sortieForm->getData()->setLieu($lieuForm->getData());

        //contrôle des Asserts des entités Lieu et Ville
        $lieuOk = $this->testAsserts($sortieForm->getData()->getLieu());

        if ($sortieForm->isSubmitted() && $sortieForm->isValid() && $lieuOk) {

            $intEtat = null;

            if ($sortieForm->getClickedButton()->getName() === "saveSortie") {
                $intEtat = 1;
                $this->addFlash('info', 'Sortie modifiée');
            } else if ($sortieForm->getClickedButton()->getName() === "publishSortie") {
                $intEtat = 2;
                $this->addFlash('success', 'Sortie publiée');
            } else if ($sortieForm->getClickedButton()->getName() === "removeSortie") {
                if ($findMyEtat->getId() === 1) {
                    $em->remove($lieuForm->getData());
                    $em->remove($sortieForm->getData());
                    $em->flush();

                    $this->addFlash('info', 'Annulation de la sortie');
                    return $this->redirectToRoute('home_index');
                } else {
                    return $this->redirectToRoute('sortie_delete', ['id' => $id]);
                }
            }

            $etatFind = $etatRepository->findOneBy(['id' => $intEtat]);
            $sortieForm->getData()->setEtat($etatFind);

            $em->flush();

            return $this->redirectToRoute('home_index');
        }

        return $this->render('sortie/index.html.twig', compact('sortieForm', 'lieuForm', 'villeForm'));
    }

    #[Route('/@delete/{id}', name: '_delete', requirements: ['id' => '\d+'])]
    public function delete(int $id, EntityManagerInterface $em, Request $request, EtatRepository $etatRepository, SortieRepository $sortieRepository, LieuRepository $lieuRepository, VilleRepository $villeRepository, ParticipantRepository $participantRepository): Response
    {
        $user = $participantRepository->findOneBy(['username' => $this->getUser()->getUserIdentifier()]);
        $findMySortie = $sortieRepository->findOneBy(['organisateur' => $user, 'id' => $id]);

        if ($findMySortie === null) {
            $this->addFlash('error', "Vous n'êtes pas l'organisateur de cette sortie");
            return $this->redirectToRoute('home_index');
        }

        $findMyEtat = $etatRepository->findOneBy(['id' => $findMySortie->getEtat()->getId()]);

        $datecloture = new DateTime($findMySortie->getDateCloture()->format('Y-m-d h:i'));
        $dateStartEvent = new DateTime($findMySortie->getDateHeureDeb()->format('Y-m-d h:i'));

        if ($findMyEtat->getId() !== 2 || $datecloture >= $dateStartEvent) {
            $this->addFlash('error', "L'état du formulaire Sortie ne peut être annulé");
            return $this->redirectToRoute('home_index');
        }

        $sortieForm = $this->createForm(SortieType::class, $findMySortie);

        if ($request->getMethod() === "POST" && $request->isSecure() === true) {
            $etatFind = $etatRepository->findOneBy(['id' => 6]);

            $findMySortie->setInfosSortie($request->request->all('sortie')['infosSortie']);
            $findMySortie->setEtat($etatFind);

            $em->flush();

            $this->addFlash('info', 'La Sortie à bien été annulé');

            return $this->redirectToRoute('home_index');
        }

        return $this->render('sortie/delete.html.twig', compact('sortieForm', 'findMySortie'));
    }

    #[Route('/detail/{id}', name: '_detail', requirements: ['id' => '\d+'])]
    public function select(
        int              $id,
        SortieRepository $sortieRepository
    ): Response
    {
        $sortie = $sortieRepository->findOneBy(["id" => $id]);
        if (is_null($sortie)) {
            $this->addFlash('error', 'Consultation impossible - sortie (' . $id . ') inexistante !');
            return $this->redirectToRoute('home_index');
        }
        $etatSortie = $sortie->getEtat()->getId();
        if ($etatSortie == 1 || $etatSortie == 7) {
            $this->addFlash('error', 'Consultation impossible - sortie à l\'état ' .$sortie->getEtat()->getLibelle(). ' !');
            return $this->redirectToRoute('home_index');
        }
        return $this->render(
            'sortie/detail.html.twig',
            compact('sortie')
        );
    }

    /** Inscription de l'utilisateur connecté à la sortie passée en paramètre
     * @param int $id
     * @param ParticipantRepository $participantRepository
     * @param SortieRepository $sortieRepository
     * @param EtatRepository $etatRepository
     * @param SortieUpdateCheck $sortieUpdateCheck
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/subscribe/{id}', name: '_subscribe', requirements: ['id' => '\d+'])]
    public function subscribe(int                    $id,
                              ParticipantRepository  $participantRepository,
                              SortieRepository       $sortieRepository,
                              EtatRepository         $etatRepository,
                              SortieUpdateCheck      $sortieUpdateCheck,
                              EntityManagerInterface $em): Response
    {
        $sortie = $sortieRepository->findOneBy(["id" => $id]);

        if (is_null($sortie)) {
            $this->addFlash('error', 'Inscription impossible - sortie (' . $id . ') inexistante !');
            return $this->redirectToRoute('home_index');
        }
        //Vérification que l'utilisateur connecté n'est pas déjà inscrit
        $utilisateur = $participantRepository->findOneBy(["username" => $this->getUser()->getUserIdentifier()]);
        $dejaInscrit = $sortieUpdateCheck->dejaInscrit($utilisateur, $sortie);

        if (!$dejaInscrit) {
            //Inscription autorisée si état "2-Ouvert" ou si état "3-Clôturé" et date de clôture non passée
            if ($sortie->getEtat()->getId() === 2 //"Ouvert"
                || ($sortie->getEtat()->getId() === 2 //"Clôturé"
                    && (new \Datetime()) < $sortie->getDateCloture())) {

                //Inscription autorisée si nombre d'inscriptions maxi n'est pas déjà atteint
                $nbParticipants = count($sortie->getParticipants());
                if ($nbParticipants < $sortie->getNbInscriptionsMax()) {
                    $sortie->addParticipant($utilisateur);
                    $nbParticipants++;
                    //si nombre inscription maxi atteint, alors Maj de l'état de sortie à "Clôturé"
                    if ($nbParticipants === $sortie->getNbInscriptionsMax()) {
                        $etatCloture = $etatRepository->findOneBy(['id' => 3]);  //"Clôturé"
                        $sortie->setEtat($etatCloture);
                    }
                    $em->persist($sortie);
                    $em->flush();
                    $this->addFlash('success', 'Vous avez bien été inscrit(e) !');
                } else {
                    $this->addFlash('error', 'Inscription impossible - le nombre maxi (' . $sortie->getNbInscriptionsMax() . ') de participants est déjà atteint !');
                }
            } else {
                $this->addFlash('error', 'Inscription impossible - cette sortie est à l\'état ' . $sortie->getEtat()->getLibelle() . ' !');
            }
        } else {
            $this->addFlash('error', 'Inscription impossible - vous êtes déjà inscrit(e) à cette sortie !');
        }
        return $this->redirectToRoute('home_index');
    }

    /** Désistement de l'utilisateur connecté de la sortie passée en paramètre
     * @param int $id
     * @param ParticipantRepository $participantRepository
     * @param SortieUpdateCheck $sortieUpdateCheck
     * @param SortieRepository $sortieRepository
     * @param EtatRepository $etatRepository
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/unsubscribe/{id}', name: '_unsubscribe', requirements: ['id' => '\d+'])]
    public function unsubscribe(int                    $id,
                                ParticipantRepository  $participantRepository,
                                SortieUpdateCheck      $sortieUpdateCheck,
                                SortieRepository       $sortieRepository,
                                EtatRepository         $etatRepository,
                                EntityManagerInterface $em): Response
    {

        $sortie = $sortieRepository->findOneBy(["id" => $id]);
        if (is_null($sortie)) {
            $this->addFlash('error', 'Désistement impossible - sortie (' . $id . ') inexistante !');
            return $this->redirectToRoute('home_index');
        }

        //Vérification que l'utilisateur connecté est bien déjà inscrit
        $utilisateur = $participantRepository->findOneBy(["username" => $this->getUser()->getUserIdentifier()]);
        //dd($utilisateur);
        $dejaInscrit = $sortieUpdateCheck->dejaInscrit($utilisateur, $sortie);

        if ($dejaInscrit) {
            //Désistement autorisé si état "2-Ouvert" ou si état "3-Clôturé" et date de clôture non passée
            if ($sortie->getEtat()->getId() === 2  //"Ouvert"
                || ($sortie->getEtat()->getId() === 3 //"Clôturé"
                    && (new \Datetime()) < $sortie->getDateCloture())) {

                $nbParticipants = count($sortie->getParticipants());
                $sortie->removeParticipant($utilisateur);
                if ($nbParticipants > 0) {
                    $nbParticipants--;
                }
                //Cas où le désistement "rouvre" les inscriptions
                if ($nbParticipants < $sortie->getNbInscriptionsMax()
                    && $sortie->getEtat()->getId() === 3 //"Clôturé"
                    && (new \Datetime()) < $sortie->getDateCloture()) {
                    $etatOuvert = $etatRepository->findOneBy(['id' => 2]); //"Ouvert"
                    $sortie->setEtat($etatOuvert);
                }
                $em->persist($sortie);
                $em->flush();
                $this->addFlash('success', 'Votre désistement a bien été enregistré !');
            } else {
                $this->addFlash('error', 'Désistement impossible - sortie à l\'état ' . $sortie->getEtat()->getLibelle() . ' !');
            }
        } else {
            $this->addFlash('error', 'Désistement impossible - vous n\'êtes pas inscrit(e) !');
        }
        return $this->redirectToRoute('home_index');
    }

    /** Passage à l'état "Ouvert" d'une sortie à l'état "En Création"
     * par l'utilisateur connecté si c'est l'organisateur
     * @param int $id
     * @param SortieRepository $sortieRepository
     * @param ParticipantRepository $participantRepository
     * @param EtatRepository $etatRepository
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/publish/{id}', name: '_publish', requirements: ['id' => '\d+'])]
    public function publish(int                    $id,
                            SortieRepository       $sortieRepository,
                            ParticipantRepository  $participantRepository,
                            EtatRepository         $etatRepository,
                            EntityManagerInterface $em): Response
    {
        $sortie = $sortieRepository->findOneBy(["id" => $id]);

        if (is_null($sortie)) {
            $this->addFlash('error', 'Publication impossible - sortie ('.$id.') inexistante !');
            return $this->redirectToRoute('home_index');
        }
        $utilisateur = $participantRepository->findOneBy(["username" => $this->getUser()->getUserIdentifier()]);
        if ($utilisateur !== $sortie->getOrganisateur()) {
            $this->addFlash('error', 'Publication réalisable uniquement par l\'organisateur !');
            return $this->redirectToRoute('home_index');
        }

        // Publication possible seulement si la sortie est à l'état "1-En création"
        if ($sortie->getEtat()->getId() === 1) {
            $etatOuvert = $etatRepository->findOneBy(["id" => 2]);  //passage à l'état "Ouvert"
            $sortie->setEtat($etatOuvert);
            $em->persist($sortie);
            $em->flush();
        } else {
            $this->addFlash('error', 'Publication impossible - sortie à l\'état ' . $sortie->getEtat()->getLibelle() . ' !');
            return $this->redirectToRoute('home_index');
        }

        return $this->redirectToRoute('home_index');
    }

}
