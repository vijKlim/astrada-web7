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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BusinessType extends LocalBusinessType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('type', LocalBusinessTypeChoiceType::class)
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'localBusiness.form.description',
                'help' => 'mardown_formatting.help',
                'attr' => ['rows' => '5']
            ])
            ->add('fulfillmentMethods', CollectionType::class, [
                'entry_type' => FulfillmentMethodType::class,
                'entry_options' => [
                    'label' => false,
                    'block_prefix' => 'fulfillment_method_item',
                ],
                'allow_add' => false,
                'allow_delete' => false,
                'prototype' => false,
            ])
            ->add('useDifferentBusinessAddress', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'localBusiness.form.use_different_business_address.label'
            ])
            ->add('businessAddress', AddressType::class, [
                'street_address_label' => 'localBusiness.form.business_address.label',
                'with_widget' => true,
                'with_description' => false,
                'label' => false,
            ])
        ;

        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $builder
                ->add('featured', CheckboxType::class, [
                    'label' => 'business.form.featured.label',
                    'required' => false
                ])
                ->add('transportationPerimeterExpression', HiddenType::class, [
                    'label' => 'localBusiness.form.deliveryPerimeterExpression'
                ])
                ->add('quotesAllowed', CheckboxType::class, [
                    'label' => 'business.form.quotes_allowed.label',
                    'required' => false,
                ])
//                ->add('depositRefundEnabled', CheckboxType::class, [
//                    'label' => 'business.form.deposit_refund_enabled.label',
//                    'required' => false,
//                ])
                ->add('delete', SubmitType::class, [
                    'label' => 'basics.delete',
                ]);
        }


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

            if ($business->hasDifferentBusinessAddress()) {
                $form->get('useDifferentBusinessAddress')->setData(true);
            }
        });

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {

                $form = $event->getForm();
                $business = $form->getData();

//                if ($form->has('allowStripeConnect')) {
//                    $allowStripeConnect = $form->get('allowStripeConnect')->getData();
//                    if ($allowStripeConnect) {
//                        $stripeConnectRoles = $business->getStripeConnectRoles();
//                        if (!in_array('ROLE_BUSINESS', $stripeConnectRoles)) {
//                            $stripeConnectRoles[] = 'ROLE_BUSINESS';
//                            $business->setStripeConnectRoles($stripeConnectRoles);
//                        }
//                    }
//                }

//                if ($form->has('enableGiropay')) {
//                    $enableGiropay = $form->get('enableGiropay')->getData();
//                    if ($enableGiropay) {
//                        $business->enableStripePaymentMethod('giropay');
//                    } else {
//                        $business->disableStripePaymentMethod('giropay');
//                    }
//                }



                $useDifferentBusinessAddress =
                    $event->getForm()->get('useDifferentBusinessAddress')->getData();

                if (!$useDifferentBusinessAddress) {
                    $business->setBusinessAddress(null);
                }
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