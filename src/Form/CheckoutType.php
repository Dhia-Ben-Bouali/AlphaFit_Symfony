<?php

namespace App\Form;

use App\Entity\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class CheckoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // Remove the cardholderName and cardNumber fields
            ->add('firstName', TextType::class, [
                'label' => 'First Name',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last Name',
            ])
            ->add('address', TextType::class, [
                'label' => 'Address',
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'Phone Number',
            ])
            ->add('paymentType', ChoiceType::class, [
                'label' => 'Payment Type',
                'choices' => [
                    'Online' => 'online',
                    'On Delivery' => 'delivery',
                ],
            ])
            // You can keep other fields like quantity and total if they are still relevant
            ->add('quantity', HiddenType::class, [
                'mapped' => false, // This field will not be mapped to the entity
            ])
            ->add('total', HiddenType::class, [
                'mapped' => false, // This field will not be mapped to the entity
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Confirm Order',
                'attr' => [
                    'class' => 'btn btn-info btn-block btn-lg',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
