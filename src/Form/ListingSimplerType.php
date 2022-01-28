<?php


namespace App\Form;


use App\Entity\Listing;
use App\Entity\LocalBusiness\CatalogInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListingSimplerType extends ListingType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

//        $builder
//            ->remove('address')
//            ->add('address', AddressType::class, [
//                'with_widget' => true,
//                'with_description' => false,
//                'label' => false,
//            ]);

        $builder->add('images', CollectionType::class,[
            'entry_type'=> ListingImageType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'prototype' => true
        ]);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($options) {
            $form = $event->getForm();

            $form
                ->remove('status');

        });

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => Listing::class,
            'owner' => null,
            'with_remember_address' => false,
        ));
    }
}