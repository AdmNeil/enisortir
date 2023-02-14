<?php

namespace App\Form;

use App\Entity\Sortie;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\DateTime;

class FiltreHomeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('site') //modif ajout entité pour aide à la saisie
            ->add('nom')  //modif label
            ->add('datemin')
            ->add('datemax')

            //->add('etat')
            // ->add('duree')
            //->add('dateCloture')
            //->add('nbInscriptionsMax')
            //->add('urlPhoto')
            //->add('infosSortie')
            ///->add('lieu')

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
