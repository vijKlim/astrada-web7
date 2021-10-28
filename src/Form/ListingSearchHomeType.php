<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListingSearchHomeType extends ListingSearchResultType
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
        return 'listing_search_home';
    }

}
