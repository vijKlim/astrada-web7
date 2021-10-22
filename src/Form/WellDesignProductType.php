<?php


namespace App\Form;


use App\Entity\LocalBusiness\CatalogInterface;
use App\Entity\Welldesign;
use App\Enum\PipeDiameter;
use App\Enum\VehicleType;
use App\Form\Type\MoneyType;
use App\Form\Type\PriceType;
use Sylius\Component\Locale\Provider\LocaleProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class WellDesignProductType extends AbstractType
{
    private $localeProvider;
    public function __construct(
        LocaleProviderInterface $localeProvider,
        TranslatorInterface $translator)
    {
        $this->localeProvider = $localeProvider;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('title', TextType::class, [
        'label' => 'form.product.name.label'
        ]);
        $builder->add('description', TextareaType::class, [
            'required' => false,
            'label' => 'form.product.description.label'
        ]);
        $builder->add('pipeDiameter', ChoiceType::class, [
            'label' => 'form.welldesign.pipeDiameter.label',
            'choices' => $this->createEnumAttributeChoices(PipeDiameter::values(), 'pipeDiameter.%s'),
            'expanded' => true,
            'multiple' => false,
            'mapped' => true
        ]);

        $builder->add('depthFrom', IntegerType::class, [
            'label' => 'form.welldesign.depthFrom.label',
            'mapped' => true
        ]);

        $builder->add('depthTo', IntegerType::class, [
            'label' => 'form.welldesign.depthTo.label',
            'mapped' => true
        ]);

        $builder->add('vehicleType', ChoiceType::class, [
            'label' => 'form.welldesign.vehicle_type.label',
            'choices' => $this->createEnumAttributeChoices(VehicleType::values(), 'vehicleType.%s'),
            'expanded' => true,
            'multiple' => true,
            'mapped' => true
        ]);

        $builder->get('vehicleType')
            ->addModelTransformer(new CallbackTransformer(
                function ($vehicleAsArray) {
                    // transform the string back to an array
                    return $vehicleAsArray ? explode(', ', $vehicleAsArray) : [];

                },
                function ($vehicleAsArray) {
                    // transform the array to a string
                    return $vehicleAsArray ? implode(', ', $vehicleAsArray) : '';
                }
            ));

        $builder->add('price', MoneyType::class, [
            'mapped' => true,
            'label' => 'form.price.label'
        ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {

            $form = $event->getForm();
            $welldesign = $event->getData();

            // This is a delete button (used in list of products)
            if (count($form) === 1 && $form->has('delete')) {

                return;
            }

        });

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Welldesign::class,
            'owner' => null,
            'reusable_packaging_choices' => [],
            'options_loader' => null,
        ));
        $resolver->setAllowedTypes('owner', ['null',CatalogInterface::class]);
        $resolver->setAllowedTypes('options_loader', ['null', 'callable']);
    }

    protected function createEnumAttributeChoices(array $values, $format)
    {
        $choices = [];
        foreach ($values as $value) {
            $label = $this->translator->trans(sprintf($format, $value->getKey()));
            $choices[$value->getKey()] = $label;
        }

        asort($choices);

        return array_flip($choices);
    }

}