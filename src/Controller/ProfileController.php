<?php

namespace App\Controller;

use App\Form\ProfileType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile', name:'profile')]
#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{

    #[Route('/@me', name: '_myprofile')]
    //Afficher mon profil quand je suis connecté
    public function myProfile(
        ParticipantRepository $participantRepository,
    ): Response
    {
        $participant = $participantRepository->findOneBy(["username"=>$this->getUser()->getUserIdentifier()]);
        return $this->render(
            'profile/myprofile.html.twig',
            compact('participant')
        );
    }


    #[Route('/update', name: '_update')]
    //Modifier mes informations de profil
    public function update(
        EntityManagerInterface $em,
        Request $request,
        ParticipantRepository $participantRepository,
    ): Response
    {
        $participant = $participantRepository->findOneBy(["username"=>$this->getUser()->getUserIdentifier()]);
        $participantForm = $this->createForm(ProfileType::class, $participant);
        $participantForm->handleRequest($request);

        if($participantForm->isSubmitted() && $participantForm->isValid()){
            $em->persist($participant);
            $em->flush();
            return $this->render('profile/myprofile.html.twig');
        }

        return $this->render(
            'profile/update.html.twig',
            compact('participantForm')
        );
    }

}
