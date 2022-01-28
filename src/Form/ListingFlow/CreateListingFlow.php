<?php


namespace App\Form\ListingFlow;


use Craue\FormFlowBundle\Event\PostBindSavedDataEvent;
use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowEvents;
use Craue\FormFlowBundle\Form\FormFlowInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CreateListingFlow extends FormFlow implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents() {
        return [
            FormFlowEvents::POST_BIND_SAVED_DATA => 'onPostBindSavedData',
        ];
    }

    public function onPostBindSavedData(PostBindSavedDataEvent $event) {

        if ($event->getFlow() !== $this) {
            return;
        }
//        echo '<pre>step '.$event->getStepNumber().'<br>';var_dump($_POST);
        if ($event->getStepNumber() === 2) {
            $formData = $event->getFormData();


//            if ($formData->addDriver) {
//                $formData->driver = new Driver();
//                $formData->driver->vehicles->add($formData->vehicle);
//            }
        }
        if ($event->getStepNumber() === 3) {
            $formData = $event->getFormData();

//            if ($formData->addDriver) {
//                $formData->driver = new Driver();
//                $formData->driver->vehicles->add($formData->vehicle);
//            }
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function loadStepsConfig() {
        $formType = CreateListingForm::class;

        return [
            [
                'label' => '',
                'form_type' => $formType,
            ],
            [
                'label' => 'listing.flow.basic_information',
                'form_type' => $formType,
            ],
            [
                'label' => 'listing.flow.listing_information',
                'form_type' => $formType,

            ],
            [
                'label' => 'listing.flow.confirmation',
            ],
        ];
    }
}