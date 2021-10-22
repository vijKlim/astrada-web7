<?php


namespace App\Form\Type;


use App\Form\DataTransformer\PriceTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sylius\Component\Currency\Context\CurrencyContextInterface;
use Sylius\Component\Currency\Converter\CurrencyConverterInterface;

class PriceScaleType
    extends AbstractType
{
    protected $currencyContext;
    protected $currency;
    protected $defaultCurrency;
    protected $pricePrecision;
    protected $currencyConverter;

    /**
     * @param string    $defaultCurrency
     * @param int       $pricePrecision
     * @param Converter $currencyConverter
     */
    public function __construct(CurrencyContextInterface $currencyContext, $pricePrecision, CurrencyConverterInterface $currencyConverter)
    {
        $this->currencyContext = $currencyContext;
        $this->defaultCurrency = $this->currencyContext->getCurrencyCode();
        $this->currency = $this->currencyContext->getCurrencyCode();
        $this->pricePrecision = $pricePrecision;
        $this->currencyConverter = $currencyConverter;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new PriceTransformer(
            $options["currency"],
            $this->defaultCurrency,
            $this->currencyConverter,
            $this->pricePrecision
        );
        $builder->addModelTransformer($transformer);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'translation_domain' => 'cocorico_listing',
                'scale' => $this->pricePrecision,
                'defaultCurrency' => $this->defaultCurrency,
                'currency' => $this->defaultCurrency,
                'attr' => array(
                    'class' => 'numbers-only'
                ),
                'include_vat' => null //if true then incl tax is displayed else excl tax is displayed
            )
        );
    }


    /**
     * Pass the include_vat to the view
     *
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (array_key_exists('include_vat', $options) && $options["include_vat"] !== null) {
            // set an "include_vat" variable that will be available when rendering this field
            $view->vars['include_vat'] = $options["include_vat"];
        }
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return MoneyType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'price_scale';
    }
}
