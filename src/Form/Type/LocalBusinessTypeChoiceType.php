<?php


namespace App\Form\Type;


use App\Enum\HomeAndConstructionBusiness;
use App\Enum\Store;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class LocalBusinessTypeChoiceType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $choices = [];

        $homeAndConstructionBusinessValues = HomeAndConstructionBusiness::values();

        foreach (HomeAndConstructionBusiness::values() as $value) {
            $key = sprintf('construction_business.%s', $value->getKey());
            $choices[$key] = $value->getValue();
        }

        foreach (Store::values() as $value) {
            $key = sprintf('store.%s', $value->getKey());
            $choices[$key] = $value->getValue();
        }

        asort($choices);

        $resolver->setDefaults([
            'choices' => $choices,
            'group_by' => function($choice, $key, $value) {
                if ($found = Store::search($value)) {
                    return $this->translator->trans('form.local_business_type.store');
                }

                return $this->translator->trans('form.local_business_type.food_establishment');
            },
            'label' => 'form.local_business_type.label',
            'help' => 'form.local_business_type.help',
            'help_html' => true,
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}