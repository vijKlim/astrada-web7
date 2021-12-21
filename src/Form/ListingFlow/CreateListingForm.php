<?php


namespace App\Form\ListingFlow;


use App\Entity\Listing;
use App\Entity\LocalBusiness;
use App\Enum\OwnerType;
use App\Form\AddressType;
use App\Form\BusinessSimplerType;
use App\Form\ListingSimplerType;
use App\Form\ListingType;
use App\Form\LocalBusinessType;
use libphonenumber\PhoneNumberFormat;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class CreateListingForm extends AbstractType
{

    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        switch ($options['flow_step']) {
            case 1:
                $businessForm = $builder->create('business', FormType::class, [
                    'label' => false,
                    'data_class' => LocalBusiness::class,
                ]);
                $businessForm->add('ownerType', ChoiceType::class,[
                    'label' => false,
                    'required' => true,
                    'choices'  => $this->getOwnerTypeChoices(),
                    'expanded' => true,
                    'multiple' => false,
                    'data' => OwnerType::INDIVIDUAL,
                ]);
                $builder->add($businessForm);
                break;
            case 2:
                $businessForm = $builder->create('business', BusinessSimplerType::class, [
                    'data_class' => LocalBusiness::class,
                ]);

                $builder->add($businessForm);
                break;
            case 3:
                $listingForm = $builder->create('listing', ListingSimplerType::class, [
                    'data_class' => Listing::class,
                ]);

                $builder->add($listingForm);
                break;
        }
    }

    private function getOwnerTypeChoices()
    {
        return [
            $this->translator->trans('localBusiness.form.ownerType.'.OwnerType::INDIVIDUAL) => OwnerType::INDIVIDUAL,
            $this->translator->trans('localBusiness.form.ownerType.'.OwnerType::LEGAL_ENTITY) => OwnerType::LEGAL_ENTITY,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => CreateListing::class,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix() {
        return 'createListing';
    }
}