<?php

namespace App\Form;

use App\Entity\CategorySearch; 
use Symfony\Component\Form\AbstractType; 
use Symfony\Component\Form\FormBuilderInterface; 
use Symfony\Component\OptionsResolver\OptionsResolver; 
use Symfony\Bridge\Doctrine\Form\Type\EntityType; 
use App\Entity\Category;
class CategorySearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder ->add('category',EntityType::class,['class' => Category::class, 'choice_label' => 'titre' , 'label' => 'Catégorie' ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            'data_class' => CategorySearch::class,
        ]);
    }
}