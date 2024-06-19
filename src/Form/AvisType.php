<?php

namespace App\Form;

use App\Entity\Avis;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('star1', CheckboxType::class, [
            'label' => '1 étoile',
            'required' => false,
        ])
        ->add('star2', CheckboxType::class, [
            'label' => '2 étoiles',
            'required' => false,
        ])
        ->add('star3', CheckboxType::class, [
            'label' => '3 étoiles',
            'required' => false,
        ])
        ->add('star4', CheckboxType::class, [
            'label' => '4 étoiles',
            'required' => false,
        ])
        ->add('star5', CheckboxType::class, [
            'label' => '5 étoiles',
            'required' => false,
        ])
        ->add('commentaire', TextType::class, [
            'required' => false,
        ])
        ->add('confirm',SubmitType::class);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Avis::class,
        ]);
    }
}
