<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit',
                'required' => false,
                'attr' => ['placeholder' => 'Rechercher un produit...']
            ])
            ->add('category', EntityType::class, [
                'label' => 'Catégorie',
                'class' => Category::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Toutes les catégories'
            ])
            ->add('minPrice', NumberType::class, [
                'label' => 'Prix min (€)',
                'required' => false,
                'attr' => ['placeholder' => '0', 'min' => 0]
            ])
            ->add('maxPrice', NumberType::class, [
                'label' => 'Prix max (€)',
                'required' => false,
                'attr' => ['placeholder' => '999', 'min' => 0]
            ])
            ->add('country', TextType::class, [
                'label' => 'Pays d\'origine',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: Japon']
            ])
            ->add('tag', ChoiceType::class, [
                'label' => 'Tag',
                'required' => false,
                'placeholder' => 'Tous les tags',
                'choices' => [
                    'Promo' => 'promo',
                    'Nouveau' => 'nouveau',
                    'Best-seller' => 'best',
                    'Collector' => 'collector',
                ]
            ])
            ->add('inStock', CheckboxType::class, [
                'label' => 'En stock uniquement',
                'required' => false,
            ])
            ->add('sortBy', ChoiceType::class, [
                'label' => 'Trier par',
                'required' => false,
                'placeholder' => 'Plus récents',
                'choices' => [
                    'Prix croissant' => 'price_asc',
                    'Prix décroissant' => 'price_desc',
                    'Nom A-Z' => 'name_asc',
                    'Nom Z-A' => 'name_desc',
                    'Plus anciens' => 'oldest',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}