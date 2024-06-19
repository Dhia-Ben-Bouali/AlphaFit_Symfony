<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use VictorPrdh\RecaptchaBundle\Form\ReCaptchaType;

class RegistrationFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('adresse', TextType::class)
            ->add('email', TextType::class)
            ->add('password', PasswordType::class)
            ->add('confirmPassword', PasswordType::class, [
                'mapped' => false, // This ensures the field is not mapped to the entity
                'constraints' => [
                    new Callback([$this, 'validatePasswordConfirmation']),
                ],
            ])
//            ->add("captcha",ReCaptchaType::class)
            ->add('register', SubmitType::class);
    }
    public function validatePasswordConfirmation($value, ExecutionContextInterface $context): void
    {
        $form = $context->getRoot();

        if ($form['password']->getData() !== $value) {
            $context->buildViolation('Passwords do not match.')
                ->atPath('confirmPassword')
                ->addViolation();
        }
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
