<?php


namespace App\Form\Type;

use Sylius\Component\Product\Resolver\ProductVariantResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PriceType extends AbstractType
{
    private $variantResolver;

    public function __construct(
        ProductVariantResolverInterface $variantResolver)
    {
        $this->variantResolver = $variantResolver;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('price', MoneyType::class, [
                'mapped' => false,
                'label' => 'form.price.label'
            ]);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            if (null === $event->getData()) {
                return;
            }
            $form = $event->getForm();
            $product = $form->getParent()->getData();

            if (null !== $product->getId()) {

                $variant = $this->variantResolver->getVariant($product);
                $form->get('price')->setData($variant->getPrice());
            }
        });
    }

    public function getBlockPrefix()
    {
        return 'astrada_price_with_tax';
    }
}