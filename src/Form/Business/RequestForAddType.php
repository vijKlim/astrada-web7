<?php


namespace App\Form\Business;


use App\Form\AddressType;
use App\Message\Request\RequestBusiness;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class RequestForAddType extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'form.business.name.label',
            ])
            ->add('address', AddressType::class, [
                'label' => false,
                'with_widget' => true,
                'with_description' => false,
            ])
            ->add('contact', EmailType::class, [
                'label' => 'form.business.contact_referent.label',
            ])
            ->setDataMapper($this)
        ;
    }

    public function mapDataToForms($viewData, $forms)
    {

    }

    public function mapFormsToData($forms, &$viewData)
    {
        $forms = iterator_to_array($forms);
        $viewData = new RequestBusiness(
            $forms['name']->getData(),
            $forms['address']->getData(),
            $forms['contact']->getData()
        );
    }
}