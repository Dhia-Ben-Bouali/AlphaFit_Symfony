<?php

namespace App\Form;
use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Entity\Categorie;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('description')
            ->add('prix')
            ->add('quantite')
            ->add('image', FileType::class, [
                'label' => 'image',
                'mapped' => false, // Important pour les champs non liés à une propriété de l'entité
                'required' => false, // Rend le champ facultatif
            ])
            ->add('categorie', EntityType::class,['class'=>Categorie::class,'choice_label'=>'libelle','multiple'=>false,'expanded'=>false,])
        ; }
        
    

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
