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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/profile', name: 'profile')]
#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{


    #[Route('/@me', name: '_myprofile')]
    //Afficher mon profil quand je suis connecté
    public function myProfile(
        ParticipantRepository $participantRepository,
    ): Response
    {
        $participant = $participantRepository->findOneBy(["username" => $this->getUser()->getUserIdentifier()]);
        return $this->render(
            'profile/myprofile.html.twig',
            compact('participant')
        );
    }


    #[Route('/update', name: '_update')]
    //Modifier mes informations de profil
    public function update(
        EntityManagerInterface $em,
        Request                $request,
        ParticipantRepository  $participantRepository,
        SluggerInterface       $slugger,
        UserPasswordHasherInterface $userPasswordHasher
    ): Response
    {
        $participant = $participantRepository->findOneBy(["username" => $this->getUser()->getUserIdentifier()]);
        $participantForm = $this->createForm(ProfileType::class, $participant);
        $participantForm->handleRequest($request);
        if ($participantForm->isSubmitted() && $participantForm->isValid()) {
            $newImgFile = $participantForm->get("imageFile")->getData();

            if ($newImgFile) {
                $newImgFilename = pathinfo($newImgFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($newImgFilename);
                //Gestion unicité du nom du fichier
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $newImgFile->guessExtension();

                try {
                    $oldImgFile = $participantRepository->findOneBy(['username' => $this->getUser()->getUserIdentifier()]);

                    $fileSystem = new Filesystem();
                    $fileSystem->remove($this->getParameter('upload_directory') . '\\' . $oldImgFile->getUrlPhoto());

                    $newImgFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                    $participant->setUrlPhoto($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', $e);
                }
            }

            $user = $participant;
            $pass = $participantForm->get('password')->getData();
            $hash = $userPasswordHasher->hashPassword($user, $pass);
            $participantForm->getData()->setPassword($hash);

            $em->persist($participant);
            $em->flush();
            return $this->render('profile/myprofile.html.twig');
        }
        return $this->render(
            'profile/update.html.twig',
            compact('participantForm')
        );
    }

    #[Route('/show/{id}', name: '_show')]
    public function show(int                   $id,
                         ParticipantRepository $participantRepository,
                         ) :Response
    {
        $participant = $participantRepository->findOneBy(["id" => $id]);
        if (is_null($participant)) {
            $this->addFlash('error', 'Profil non disponible - participant ('.$id.') inexistant !');
            return $this->redirectToRoute('home_index');
        }

        return $this->render('profile/show.html.twig',
                             compact('participant')
        );
    }

}
