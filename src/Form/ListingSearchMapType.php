<?php


namespace App\Form;


use Symfony\Component\Form\FormBuilderInterface;

class ListingSearchMapType extends ListingSearchResultType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->remove('price_range')
            ->remove('date_range')
            ->remove('sort_by');

        if ($this->timeUnitFlexibility) {
            $builder->remove('flexibility');
        }

        if (!$this->timeUnitIsDay) {
            $builder->remove('time_range');
        }

    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'listing_search_map';
    }

}
