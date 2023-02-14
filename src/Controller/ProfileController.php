<?php

namespace App\Controller;

use App\Entity\Participant;
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

    #[Route('/myprofile/{id}', name: '_myprofile')]
    public function myProfile(
        int $id,
        ParticipantRepository $participantRepository,
    ): Response
    {
        $participant = $participantRepository->findOneBy(["id"=>$id]);
        return $this->render(
            'profile/myprofile.html.twig',
            compact('participant')
        );
    }


    #[Route('/update', name: '_update')]
    public function update(
        EntityManagerInterface $em,
        Request $request,
    ): Response
    {
        $participant = new Participant();
        $participantForm = $this->createForm(ProfileType::class, $participant);
        $participantForm->handleRequest($request);

        if($participantForm->isSubmitted() && $participantForm->isValid()){
            $em->persist($participantForm);
            $em->flush();
            return $this->render('profile/myprofile.html.twig');
        }

        return $this->render(
            'profile/update.html.twig',
            compact('participantForm')
        );
    }

//    #[Route('/{id}', name: '_detail')]
//    public function detail(
//        int $id,
//        ParticipantRepository $participantRepository,
//    ): Response
//    {
//        $participant = $participantRepository->findOneBy(["id"=>$id]);
//        return $this->render(
//            'profile/detail.html.twig',
//            compact('participant')
//        );
//    }
}
