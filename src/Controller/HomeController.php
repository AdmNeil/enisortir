<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\Sortie;
use App\Form\FiltreHomeType;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/', name: 'home')]
class HomeController extends AbstractController
{
    #[Route('/', name: '_index')]
    public function index(
        SortieRepository $sortieRepository,
        SiteRepository   $siteRepository,
        Request          $request
    ): Response
    {
        $sortie = new Sortie();


        $filtreHomeForm = $this->createForm(FiltreHomeType::class, $sortie);
        $filtreHomeForm->handleRequest($request);

        /*if () {
            $sorties = $sortieRepository->findBy([], ["dateHeureDeb" => "ASC"], null, 0);
        }*/
        $sorties = null;
        return $this->render('home/index.html.twig',
        compact('filtreHomeForm','sorties'));
    }
}
