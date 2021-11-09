<?php


namespace App\Form;


use App\Entity\Listing;
use App\Entity\LocalBusiness\CatalogInterface;
use Doctrine\ORM\EntityManagerInterface;
use libphonenumber\PhoneNumberFormat;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Product\Factory\ProductFactoryInterface;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\SerializerInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ListingType extends AbstractType
{


    protected $entityManager;
    protected $serializer;
    private $productFactory;
    protected $country;
    protected $debug;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ProductFactoryInterface $productFactory,
        string $country,
        bool $debug = false)
    {

        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->productFactory = $productFactory;
        $this->country = $country;
        $this->debug = $debug;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('description', TextareaType::class, [
                'required' => false,
            ])

//            ->add('website', UrlType::class, ['required' => false, 'label' => 'listing.form.website',])
            ->add('telephone', PhoneNumbertype::class, [
                'default_region' => strtoupper($this->country),
                'format' => PhoneNumberFormat::NATIONAL,
                'required' => false,
                'label' => 'localBusiness.form.telephone',
            ])
            ->add('address', AddressBookType::class, [
                'with_addresses' =>  [],
                'with_remember_address' => $options['with_remember_address'],
            ])
            ->add('welldesign', WellDesignProductType::class, [
//                'label' => false
            ]);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            $listing = $event->getData();


            $choices = Listing::getAvailableStatusValues($listing->getStatus());

            $form
                ->add(
                    'status',
                    ChoiceType::class,
                    array(
                        'label' => 'listing.form.status',
                        'choices' => array_flip($choices),
                    )
                );
            if (null !== $listing->getId()) {
                $form->add('imageFile', VichImageType::class, [
                    'required' => false,
                    'download_uri' => false,
                ]);


            }
        });



        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {

                $listing = $event->getForm()->getData();
                $address = $listing->getAddress();

                if (null === $listing->getId()) {

                    $addressName = $address->getName();
                    $addressTelephone = $address->getTelephone();

                    if (empty($addressName)) {
                        $address->setName($listing->getTitle());
                    }
                    if (empty($addressTelephone)) {
                        $address->setTelephone($listing->getTelephone());
                    }
                }

                if (null !== $options['owner']) {
                    $options['owner']->addListing($listing);
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => Listing::class,
            'owner' => null,
            'with_remember_address' => false,
        ));
        $resolver->setAllowedTypes('owner', CatalogInterface::class);
    }
}