<?php

namespace App\Form;

use App\Entity\Listing\ListingPricingRule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListingPricingRuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('expression', HiddenType::class)
            ->add('type', ChoiceType::class, [
                'label' => 'form.pricing_rule.type.label',
                'required' => true,
                'choices'  => [
                    'form.pricing_rule.type.transportation_cost' => ListingPricingRule::TYPE_TRANSPORTATION_COST,
                    'form.pricing_rule.type.well_cost' => ListingPricingRule::TYPE_WELL_COST,
                ],

                'expanded' => false,
                'multiple' => false,
            ])
            ->add('price', TextType::class, [
                'label' => 'form.pricing_rule.price.label'
            ])
            ->add('position', HiddenType::class, [
                'required' => false
            ]);

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event) {

                $form = $event->getForm();
                $pricingRule = $form->getData();

                if ($pricingRule !== null && is_numeric($pricingRule->getPrice())) {
                    $form->get('price')->setData(number_format((float)$pricingRule->getPrice() / 100, 2));
                }
            }
        );

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $pricingRule = $event->getForm()->getData();
                $price = $pricingRule->getPrice();
                $price = str_replace(',', '.', $price);

                // if price is a fixed price, store it as cents
                // if it is an expression, we assume a power user entered it correctly with price in cents
                if (is_numeric($price)) {
                    $price = (float)$price * 100;
                    $pricingRule->setPrice((string)$price);
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ListingPricingRule::class,
        ));
    }
}
