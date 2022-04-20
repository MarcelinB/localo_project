<?php

namespace App\Form;

use App\Entity\User;
use Doctrine\DBAL\Driver\Mysqli\Initializer\Options;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("email", type: EmailType::class)
            ->add("firstName", type: TextType::class)
            ->add("lastName", type: TextType::class)
            ->add("password", type: PasswordType::class)
            ->add("lastName", type: TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(["date_class", User::class]);
    }
}
