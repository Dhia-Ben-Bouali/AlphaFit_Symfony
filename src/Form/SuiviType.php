<?php

namespace App\Form;

use App\Entity\Suivi;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;

class SuiviType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plan_coach',CheckboxType::class, [
                'required' => false,
                'data' => false,
                ])
            ->add('plan_nutritionniste',CheckboxType::class, [
                'required' => false,
                'data' => false,
                ])
            ->add('title')
            ->add('start', DateTimeType::class, [
                'widget' => 'single_text',
                'data' => new \DateTime(), // Définit la date par défaut comme aujourd'hui
                'constraints' => [
                    new GreaterThan([
                        'value' => new \DateTime('today'),
                        'message' => 'The start date and time must be before the current date.',
                    ]),
                ],
            ])
            
            ->add('end', DateTimeType::class, [
                'widget' => 'single_text',
                'data' => new \DateTime(), // Définit la date par défaut comme aujourd'hui
                'constraints' => [
                    new GreaterThan([
                        'value' => new \DateTime('today'),
                        'message' => 'The End date and time must be before the current date.',
                    ]),
                ],
            ])
        
                
            ->add('description')
            ->add('all_day')
            ->add('background_colar', ColorType::class)
            ->add('border_color',ColorType::class)
            ->add('text_color',ColorType::class,['empty_data' => ''])
           # ->add('user',EntityType::class, [
              # "class" => User::class,
              # "choice_label" => "id"
                
              # ] )
            ->add('reset',ResetType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Suivi::class,
        ]);
    }
}
