<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit',
                'attr' => ['placeholder' => 'Ex: Figurine Luffy Gear 5']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 4]
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix',
                'currency' => 'EUR'
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock',
                'attr' => ['min' => 0]
            ])
            ->add('stockThreshold', IntegerType::class, [
                'label' => 'Seuil d\'alerte stock',
                'attr' => ['min' => 0]
            ])
            ->add('country', TextType::class, [
                'label' => 'Pays d\'origine',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: Japon']
            ])
            ->add('expirationDate', DateType::class, [
                'label' => 'Date d\'expiration',
                'required' => false,
                'widget' => 'single_text'
            ])
            ->add('tag', ChoiceType::class, [
                'label' => 'Tag',
                'required' => false,
                'placeholder' => 'Aucun',
                'choices' => [
                    'Promo' => 'promo',
                    'Nouveau' => 'nouveau',
                    'Best-seller' => 'best',
                    'Collector' => 'collector',
                ]
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'Annonce active',
                'required' => false
            ])
            ->add('category', EntityType::class, [
                'label' => 'Catégorie',
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder' => '-- Choisir une catégorie --',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}