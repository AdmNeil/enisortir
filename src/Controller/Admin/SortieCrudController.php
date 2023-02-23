<?php

namespace App\Controller\Admin;

use App\Entity\Sortie;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SortieCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Sortie::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('etat'),
            AssociationField::new('lieu'),
            AssociationField::new('site'),
            AssociationField::new('organisateur'),
            TextField::new('nom'),
            IntegerField::new('duree'),
            DateTimeField::new('dateHeureDeb'),
            DateTimeField::new('dateCloture'),
            IntegerField::new('nbInscriptionsMax'),
            TextField::new('infosSortie')
        ];
    }

}
