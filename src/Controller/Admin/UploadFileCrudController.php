<?php

namespace App\Controller\Admin;

use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\UploadFile;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UploadFileCrudController extends AbstractCrudController
{
    private const PATH = 'uploads/files/';

    public static function getEntityFqcn(): string
    {
        return UploadFile::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            yield ImageField::new('fileAddUser')->setUploadDir(self::PATH)
        ];
    }

    public function getRedirectResponseAfterSave(AdminContext $context, string $action): RedirectResponse
    {
        $this->readFile($context->getRequest()->files->get('UploadFile')['fileAddUser']['file']->getClientOriginalName());

        return parent::getRedirectResponseAfterSave($context, $action);
    }


    private function readFile($fileName)
    {
        $filePath = $this->getParameter('kernel.project_dir') . '/' . self::PATH.$fileName;

        if (file_exists($filePath)) {
            $read = file_get_contents($filePath);
            $entityManager = $this->container->get('doctrine')->getManagerForClass(Participant::class);

            foreach (json_decode($read) as $i => $item) {
                $participant = new Participant();

                $rsltSite = $entityManager->getRepository(Site::class)->findOneBy(['id' => $item->site_id]);

                $participant->setSite($rsltSite);
                $participant->setUsername($item->username);
                $participant->setRoles($item->roles);
                $participant->setPassword($item->password);
                $participant->setNom($item->nom);
                $participant->setPrenom($item->prenom);
                $participant->setTelephone($item->telephone);
                $participant->setMail($item->mail);
                $participant->setIsAdmin($item->is_admin);
                $participant->setIsActif($item->is_actif);
                $participant->setIsBlocked($item->is_blocked);
                $participant->setUrlPhoto($item->url_photo);
                $participant->setImageFile($item->image_file);

                $ifExistUser = $entityManager->getRepository(Participant::class)->findOneBy(['username' => $item->username]);

                if($ifExistUser == null) {
                    $entityManager->persist($participant);
                } else {
                    $this->addFlash('error', "L'utilisateur ". $item->username ." est déjà présent");
                }
            }

            $fileUploaded = $entityManager->getRepository(UploadFile::class)->findOneBy(['fileAddUser' => $fileName]);
            $entityManager->remove($fileUploaded);

            $entityManager->flush();

            $fileSystem = new Filesystem();
            $fileSystem->remove($filePath);
        }
    }
}
