<?php

namespace App\Form;

use App\Entity\Farm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateFarmType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, options: [
                'label' => 'Nom de la ferme',
                'empty_data' => ''
            ])
            ->add('imageFile', type:FileType::class, options: [
                'label' => 'Photo',
            ])
            ->add('adress', TextType::class, options: [
                'label' => 'Adresse',
                'empty_data' => ''
            ])
            ->add('ville', TextType::class, options: [
                'label' => 'Ville',
                'empty_data' => ''
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Farm::class,
        ]);
    }
}
