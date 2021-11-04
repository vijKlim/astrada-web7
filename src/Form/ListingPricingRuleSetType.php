<?php

namespace App\Form;

use App\Entity\Listing\ListingPricingRuleSet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListingPricingRuleSetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'form.pricing_rule_set.name.label'
            ])
            ->add('strategy', ChoiceType::class, [
                'required' => true,
                'choices'  => [
                    'form.pricing_rule_set.strategy.find.label' => 'find',
                    'form.pricing_rule_set.strategy.map.label' => 'map',
                ],
                'label' => 'form.pricing_rule_set.strategy.label',
                'help' => 'form.pricing_rule_set.strategy.help',
                'multiple' => false,
                'expanded' => true,
            ])
            ->add('rules', CollectionType::class, array(
                'label' => 'form.pricing_rule_set.rules.label',
                'entry_type' => ListingPricingRuleType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ListingPricingRuleSet::class,
        ));
    }
}