<?php


namespace App\Form\Type;


use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class DateHiddenType extends DateType
{
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'date_hidden';
    }

    public function getParent()
    {
        return HiddenType::class;
    }
}