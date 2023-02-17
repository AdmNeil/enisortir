<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\LieuType;
use App\Form\SortieType;
use App\Form\VilleType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/sortie', name: 'sortie')]
class SortieController extends AbstractController
{
    /**
     * @throws NonUniqueResultException
     */
    #[Route('/', name: '_index')]
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


    #[Route('/detail/{id}', name: '_detail', requirements: ['id'=>'\d+'])]
    public function select(
        int $id,
        SortieRepository $sortieRepository
    ): Response
    {
        $sortie = $sortieRepository->findOneBy(["id"=>$id]);
        $etatSortie = $sortie->getEtat()->getId();
        if( $etatSortie==1 || $etatSortie==7) {
            $this->addFlash('error', 'Sortie non disponible.');
            return $this->redirectToRoute('home_index');
        }
        return $this->render(
            'detail.html.twig',
            compact('sortie')
        );
    }
}
