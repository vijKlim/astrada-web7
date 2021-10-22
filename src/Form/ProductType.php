<?php

namespace App\Form;


use App\Entity\LocalBusiness\CatalogInterface;
use App\Entity\Sylius\Product;
use App\Entity\Sylius\ProductOption;
use App\Enum\Allergen;
use App\Enum\RestrictedDiet;
use App\Form\Type\PriceType;
use App\Form\Type\PriceWithTaxType;
use App\Sylius\Product\ProductInterface;
use Doctrine\Common\Collections\Collection;
use Ramsey\Uuid\Uuid;
use Sylius\Component\Attribute\Model\AttributeValueInterface;
use Sylius\Component\Locale\Provider\LocaleProviderInterface;
use Sylius\Component\Product\Factory\ProductVariantFactoryInterface;
use Sylius\Component\Product\Model\ProductAttributeValue;
use Sylius\Component\Product\Resolver\ProductVariantResolverInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductType extends AbstractType
{

    private $variantFactory;
    private $productAttributeRepository;
    private $productAttributeValueFactory;
    private $localeProvider;
    private $hasChangedName = false;
    protected $type = \App\Enum\Product::COMMON;

    public function __construct(
        ProductVariantFactoryInterface $variantFactory,
        RepositoryInterface $productAttributeRepository,
        FactoryInterface $productAttributeValueFactory,
        LocaleProviderInterface $localeProvider,
        TranslatorInterface $translator)
    {
        $this->variantFactory = $variantFactory;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->productAttributeValueFactory = $productAttributeValueFactory;
        $this->localeProvider = $localeProvider;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', HiddenType::class, [
                'required' => true,
                'data' => $this->type,
            ])
            ->add('name', TextType::class, [
                'label' => 'form.product.name.label'
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'form.product.description.label'
            ])
            ->add('enabled', CheckboxType::class, [
                'required' => false,
                'label' => 'form.product.enabled.label',
            ])
            ->add('delete', SubmitType::class, [
                'label' => 'basics.delete',
            ]);

// While price & tax category are defined in ProductVariant,
        // we display the fields at the Product level
        // For now, all variants share the same values
        $builder->add('price', PriceType::class, [
            'mapped' => false,
        ]);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($options) {
            if (null === $event->getData()) {
                return;
            }
            $form = $event->getForm();
            $product = $event->getData();

            $form->add('options', CollectionType::class, [
                'entry_type' => ProductOptionWithPositionType::class,
                'entry_options' => [ 'label' => false ],
                'mapped' => false,
                'data' => $this->getSortedOptions($product, $options),
            ]);

        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {

            $form = $event->getForm();
            $data = $event->getData();


            // This is a delete button (used in list of products)
            if (count($data) === 1 && isset($data['delete'])) {
                foreach (array_keys($form->all()) as $key) {
                    if ($key !== 'delete') {
                        $form->remove($key);
                    }
                }
                return;
            }

            $product = $form->getData();

            $name = $data['name'];

            // Skip new products
            if (null === $product->getId()) {
                return;
            }

            // Skip if name has not changed
            if ($name === $product->getName()) {
                return;
            }

            $form->add('confirm', SubmitType::class, [
                'label' => 'form.product.confirm.label',
            ]);

            // With PRE_SUBMIT we can't use isClicked()
            if (isset($data['confirm'])) {
                return;
            }

            // This will add an error to the "name" field
            $this->hasChangedName = true;
        });

        $builder->get('name')->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            if ($this->hasChangedName) {
                $event->getForm()->addError(new FormError(
                    $this->translator->trans('product.name.modified', [], 'validators'),
                    'product.name.modified',
                    []
                ));
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {

            $form = $event->getForm();
            $product = $event->getData();

            if ($form->has('options')) {
                $opts = $form->get('options')->getData();
                foreach ($opts as $opt) {
                    if ($opt['enabled']) {
                        $product->addOptionAt($opt['option'], $opt['position']);
                    } else {
                        $product->removeOption($opt['option']);
                    }
                }
            }

            // This is a delete button (used in list of products)
            if (count($form) === 1 && $form->has('delete')) {

                return;
            }


            $price = $form->get('price')->get('price')->getData();


            if (null === $product->getId()) {

                $uuid = Uuid::uuid4()->toString();

                $product->setCode($uuid);
                $product->setSlug($uuid);

                $variant = $this->variantFactory->createForProduct($product);

                $variant->setName($product->getName());
                $variant->setCode(Uuid::uuid4()->toString());
                $variant->setPrice($price);
//                $variant->setPosition(1);
                $product->addVariant($variant);

            } else {
                foreach ($product->getVariants() as $variant) {
                    $variant->setName($product->getName());
                    $variant->setPrice($price);
                }
            }


            if (null !== $options['owner']) {
                $options['owner']->addProduct($product);
            }
        });
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

    protected function postSetDataEnumAttribute(Product $product, $attributeCode, FormInterface $form)
    {
        $attributeValue = $product
            ->getAttributeByCodeAndLocale($attributeCode, $this->localeProvider->getDefaultLocaleCode());

        if (null !== $attributeValue) {
            $form->setData($attributeValue->getValue());
        }
    }

    protected function postSubmitEnumAttribute(Product $product, $attributeCode, $data)
    {
        $attributeValue = $product
            ->getAttributeByCodeAndLocale($attributeCode, $this->localeProvider->getDefaultLocaleCode());

        if (null === $attributeValue) {
            $attribute =
                $this->productAttributeRepository->findOneBy(['code' => $attributeCode]);
            $attributeValue =
                $this->productAttributeValueFactory->createNew();

            $attributeValue->setAttribute($attribute);
            $attributeValue->setLocaleCode($this->localeProvider->getDefaultLocaleCode());
        }
        if($attributeValue->getType() == AttributeValueInterface::STORAGE_INTEGER){
            $attributeValue->setValue((int)$data);
        }else{
            $attributeValue->setValue($data);
        }


        $product->addAttribute($attributeValue);
    }

    private function getSortedOptions(ProductInterface $product, array $options)
    {
        if (is_callable($options['options_loader'])) {

            return call_user_func_array($options['options_loader'], [ $product ]);
        }

        return [];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Product::class,
            'owner' => null,
            'reusable_packaging_choices' => [],
            'options_loader' => null,
        ));
        $resolver->setAllowedTypes('owner', ['null',CatalogInterface::class]);
        $resolver->setAllowedTypes('options_loader', ['null', 'callable']);
    }
}
