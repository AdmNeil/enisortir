<?php

namespace App\Controller;

use App\Form\ProfileType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

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
        SluggerInterface $slugger,
    ): Response
    {
        $participant = $participantRepository->findOneBy(["username"=>$this->getUser()->getUserIdentifier()]);
        $participantForm = $this->createForm(ProfileType::class, $participant);
        $participantForm->handleRequest($request);
        if($participantForm->isSubmitted() && $participantForm->isValid()){
            $brochureFile = $participantForm->get("imageFile")->getData();
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();
                try {
                    //TO DO METHODE REMOVE
//                    $fileName = $brochureFile.$safeFilename;
//                    $brochureFile1 = new Filesystem();
//                    $projectDir = $this->getParameter('kernel.project_dir');
//                    $brochureFile1 -> remove($projectDir.'/public/img'.$fileName);
//                    $brochureFile->remove(['/public/img/', $participant->getUrlPhoto()]);
                    $brochureFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                    $participant->setUrlPhoto($newFilename);
                } catch (FileException $e) {
                    dd($e);
                }
            }
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
