<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sylius\Component\Currency\Context\CurrencyContextInterface;

class PriceRangeType extends AbstractType
{
    protected $currencyContext;

    public function __construct(CurrencyContextInterface $currencyContext)
    {
        $this->currencyContext = $currencyContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'min',
                PriceScaleType::class,
                array(
                    'label' => 'listing.form.price',
//                    'currency' => $this->currencyContext->getCurrencyCode(),
                    'scale' => 0
                )
            )
            ->add(
                'max',
                PriceScaleType::class,
                array(
                    /** @Ignore */
                    'label' => false,
//                    'currency' => $this->currencyContext->getCurrencyCode(),
                    'scale' => 0
                )
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'App\Form\Model\PriceRange',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'price_range';
    }
}
