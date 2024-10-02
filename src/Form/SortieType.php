<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            
            //Nom
            ->add(
                'nom',
                TextType::class, [
                    'label' => 'Nom de la sortie',
                    'attr'  => [
                        'class' => '
                            form-control
                            px-1
                            py-1
                            mb-3
                            text-base
                            font-normal
                            text-gray-700
                            bg-white bg-clip-padding
                            border border-solid border-gray-300
                            rounded
                            transition
                            ease-in-out
                            m-0
                            focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none
                        ',
                    ],
                ])
            
            //Date et heure
            ->add('dateHeureDebut',
                  DateTimeType::class, [
                      'html5'  => true,
                      'widget' => 'single_text',
                      'label'  => 'Date et heure de la sortie : ',
                      'attr'   => [
                          'class' => '
                            form-control
                            px-1
                            py-1
                            mb-3
                            text-base
                            font-normal
                            text-gray-700
                            bg-white bg-clip-padding
                            border border-solid border-gray-300
                            rounded
                            transition
                            ease-in-out
                            m-0
                            focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none
                        ',
                      ],
                  ])
            
            //Date limite d'inscription
            ->add(
                'dateLimiteInscription',
                DateTimeType::class, [
                    'html5'  => true,
                    'widget' => 'single_text',
                    'label'  => 'Date limite d\'inscription',
                    'attr'   => [
                        'class' => '
                                form-control
                                px-1
                                py-1
                                mb-3
                                text-base
                                font-normal
                                text-gray-700
                                bg-white bg-clip-padding
                                border border-solid border-gray-300
                                rounded
                                transition
                                ease-in-out
                                m-0
                                focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none
                            ',
                    ],
                ])
            
            //Nombre de place
            ->add(
                'nbInscriptionsMax',
                IntegerType::class, [
                    'label' => 'Nombre de places : ',
                    'attr'  => [
                        'min'   => '1',
                        'max'   => '50',
                        'class' => '
                            form-control
                            px-1
                            py-1
                            mb-3
                            w-20
                            text-base
                            font-normal
                            text-gray-700
                            bg-white bg-clip-padding
                            border border-solid border-gray-300
                            rounded
                            transition
                            ease-in-out
                            m-0
                            focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none
                        ',
                    ],
                ])
            
            //Duree
            ->add(
                'duree',
                IntegerType::class, [
                    'label' => 'Duree : ',
                    'data'  => '90',
                    'attr'  => [
                        'class' => '
                            form-control
                            px-1
                            py-1
                            mb-3
                            w-20
                            text-base
                            font-normal
                            text-gray-700
                            bg-white bg-clip-padding
                            border border-solid border-gray-300
                            rounded
                            transition
                            ease-in-out
                            m-0
                            focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none
                        ',
                    ],
                ])
            
            //Description et infos
            ->add(
                'infosSortie',
                TextAreaType::class, [
                    'label' => 'Description et infos : ',
                    'attr'  => [
                        'class'       => '
                            form-control
                            block
                            w-full
                            px-3
                            py-1.5
                            mb-3
                            text-base
                            font-normal
                            text-gray-700
                            bg-white bg-clip-padding
                            border border-solid border-gray-300
                            rounded
                            transition
                            ease-in-out
                            m-0
                            focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none
                          ',
                        'rows'        => 5,
                        'placeholder' => 'Visite d\'un musée, balade à vélo, ...',
                    ],
                ])
            
            //Campus
//            ->add(
//                'campus',
//                EntityType::class,[
//                    'label' => 'Campus',
//                    'class' => Campus::class,
//                    'choice_label' => 'nom',
//                ]
//
//            )
            
            //Lieu
            ->add(
                'lieu',
                EntityType::class, [
                    'class'        => Lieu::class,
                    'choice_label' => 'nom',
                    'attr'         => [
                        'class'       => '
                            form-control
                            block
                            w-full
                            px-3
                            py-1.5
                            mb-3
                            text-base
                            font-normal
                            text-gray-700
                            bg-white bg-clip-padding
                            border border-solid border-gray-300
                            rounded
                            transition
                            ease-in-out
                            m-0
                            focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none
                          ',
                        'rows'        => 5,
                        'placeholder' => 'Visite d\'un musée, balade à vélo, ...',
                    ],
                ])
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
                                   'data_class' => Sortie::class,
                               ]);
    }
}
