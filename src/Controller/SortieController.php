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
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

        if(sizeof($VilleExist) === 1) {
            $lieu->setVille($VilleExist[0]);
        } else {
            $lieu->setVille($villeForm->getData());
        }

        $sortie->setLieu($lieuForm->getData());

        if($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $intEtat = null;

            if($sortieForm->getClickedButton()->getName() === "saveSortie") {
                $intEtat = 1;
                $this->addFlash('info', 'Sortie sauvegardée');
            } else if ($sortieForm->getClickedButton()->getName() === "publishSortie") {
                $intEtat = 2;
                $this->addFlash('success', 'Sortie publiée');
            }

            $etatFind = $etatRepository->findOneBy(['id' => $intEtat]);
            $sortie->setEtat($etatFind);

            if(sizeof($VilleExist) === 0) $em->persist($ville);

            $em->persist($lieu);
            $em->persist($sortie);
            $em->flush();

            return $this->redirectToRoute('home_index');
        }

        return $this->render('sortie/index.html.twig', compact('sortieForm', 'lieuForm', 'villeForm'));
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

        if($findMySortie === null) {
            $this->addFlash('error', "Vous n'êtes pas l'organisateur de cette sortie");
            return $this->redirectToRoute('home_index');
        }

        $findMyEtat = $etatRepository->findOneBy(['id' => $findMySortie->getEtat()->getId()]);

        $datecloture = new DateTime($findMySortie->getDateCloture()->format('Y-m-d h:i'));
        $dateStartEvent = new DateTime($findMySortie->getDateHeureDeb()->format('Y-m-d h:i'));

        if(preg_match("/^[^1-2]$/i", $findMyEtat->getId()) || $datecloture >= $dateStartEvent ) {
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

        $VilleExist = $villeRepository->findIfExistVille($villeForm->get('codePostal')->getData());

        if(sizeof($VilleExist) === 1) {
            $lieuForm->getData()->setVille($VilleExist[0]);
        } else {
            $lieuForm->getData()->setVille($villeForm->getData());
        }

        $sortieForm->getData()->setLieu($lieuForm->getData());

        if($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $intEtat = null;

            if($sortieForm->getClickedButton()->getName() === "saveSortie") {
                $intEtat = 1;
                $this->addFlash('info', 'Sortie sauvegardée');
            } else if ($sortieForm->getClickedButton()->getName() === "publishSortie") {
                $intEtat = 2;
                $this->addFlash('success', 'Sortie publiée');
            } else if($sortieForm->getClickedButton()->getName() === "removeSortie") {
                if($findMyEtat->getId() === 1) {
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

            if(sizeof($VilleExist) === 0) $em->persist($villeForm);

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

        if($findMySortie === null) {
            $this->addFlash('error', "Vous n'êtes pas l'organisateur de cette sortie");
            return $this->redirectToRoute('home_index');
        }

        $findMyEtat = $etatRepository->findOneBy(['id' => $findMySortie->getEtat()->getId()]);

        $datecloture = new DateTime($findMySortie->getDateCloture()->format('Y-m-d h:i'));
        $dateStartEvent = new DateTime($findMySortie->getDateHeureDeb()->format('Y-m-d h:i'));

        if($findMyEtat->getId() !== 2 || $datecloture >= $dateStartEvent) {
            $this->addFlash('error', "L'état du formulaire Sortie ne peut être annulé");
            return $this->redirectToRoute('home_index');
        }

        $sortieForm = $this->createForm(SortieType::class, $findMySortie);

        if($request->getMethod() === "POST" && $request->isSecure() === true) {
            $etatFind = $etatRepository->findOneBy(['id' => 6]);
            
            $findMySortie->setInfosSortie($request->request->all('sortie')['infosSortie']);
            $findMySortie->setEtat($etatFind);

            $em->flush();

            $this->addFlash('info', 'La Sortie à bien été annulé');

            return $this->redirectToRoute('home_index');
        }

        return $this->render('sortie/delete.html.twig', compact('sortieForm', 'findMySortie'));
    }
}
