<?php


namespace App\Form;


use App\Entity\LocalBusiness;
use App\Enum\HomeAndConstructionBusiness;
use App\Form\Business\FulfillmentMethodType;
use App\Form\Type\LocalBusinessTypeChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BusinessSimplerType extends LocalBusinessType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

//        $builder
//            ->add('businessAddress', AddressType::class, [
//                'street_address_label' => 'localBusiness.form.business_address.label',
//                'with_widget' => true,
//                'with_description' => false,
//                'label' => false,
//            ])
//        ;


        $builder->remove('enabled');


        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($options) {

            $business = $event->getData();
            $form = $event->getForm();

            if (null !== $business->getId()) {

//                if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
//                    $form->add('allowStripeConnect', CheckboxType::class, [
//                        'label' => 'business.form.allow_stripe_connect.label',
//                        'mapped' => false,
//                        'required' => false,
//                        'data' => in_array('ROLE_RESTAURANT', $business->getStripeConnectRoles())
//                    ]);
//                }

//                if ($this->authorizationChecker->isGranted('ROLE_ADMIN') && ($this->debug || 'de' === $this->country)) {
//                    $form
//                        ->add('enableGiropay', CheckboxType::class, [
//                            'label' => 'business.form.giropay_enabled.label',
//                            'mapped' => false,
//                            'required' => false,
//                            'data' => $business->isStripePaymentMethodEnabled('giropay'),
//                        ]);
//                }

                $isHomeAndConstructionBusiness = HomeAndConstructionBusiness::isValid($business->getType());

//                if ($isHomeAndConstructionBusiness) {
//
//                }
            }


        });

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {

                $form = $event->getForm();
                $business = $form->getData();


//                $useDifferentBusinessAddress =
//                    $event->getForm()->get('useDifferentBusinessAddress')->getData();
//
//                if (!$useDifferentBusinessAddress) {
//                    $business->setBusinessAddress(null);
//                }

                $business->setBusinessAddress(null);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'data_class' => LocalBusiness::class,
            'loopeat_enabled' => false,
            'edenred_enabled' => false,
        ));
    }
}