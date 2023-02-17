<?php

namespace App\Form;

use App\Entity\Site;
use App\Entity\Sortie;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\DateTime;

class FiltreHomeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('site', EntityType::class,
                [
                    "class" => Site::class,
                    "choice_label" => "nom"
                ])

//            ->add('nom')
//            ->add('dateHeurDeb'
//            ->add('etat')
//            ->add('duree')
//            ->add('dateCloture')
//            ->add('nbInscriptionsMax')
//            ->add('urlPhoto')
//            ->add('infosSortie')
//            ->add('lieu')
//            ->add('organisateur')
//            ->add('participants')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
