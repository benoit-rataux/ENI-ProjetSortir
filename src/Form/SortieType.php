<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            //Nom
            ->add(
                'nom',
                TextType::class, [
                    'label' => 'Nom de la sortie',
                    ])

            //Date et heure
            ->add('dateHeureDebut',
                DateTimeType::class,[
                    'widget' => 'single_text',
                    'label' => 'Date et heure de la sortie : ',
                    ])

            //Date limite d'inscription
            ->add(
                'dateLimiteInscription',
                DateTimeType::class,[
                    'widget' => 'single_text',
                    'label' => 'Date limite d\'inscription',
                    ])

            //Nombre de place
            ->add(
                'nbInscriptionsMax',
                IntegerType::class, [
                    'label' => 'Nombre de places : ',
                    ])

            //Duree
            ->add(
                'duree',
                IntegerType::class, [
                    'label' => 'Duree : ',
                    'data' => '60',
                    ])

            //Description et infos
            ->add(
                'infosSortie',
                TextAreaType::class,[
                    'label' => 'Description et infos : ',
                    ])


            //Campus
            //Ville
            //Lieu
            /*
            + Ajout lieu
            - Rue
            - Code postal
            - Latitude
            - Longitude
            */

/*            ->add('ville', EntityType::class, [
                'label' => 'Ville',
                'class' => Ville::class,
                'choice_label' => 'nom',
            ])

            ->add('lieu', EntityType::class,[
                'label' => 'Lieu',
                'class' => Lieu::class,
                'choice_label' => 'nom',
            ])*/
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
