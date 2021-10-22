<?php


namespace App\Form;


use App\Entity\Booking;
use App\Form\Type\DateRangeType;
use App\Service\BookingManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class BookingNewType extends AbstractType
{

    public static $messageError = 'booking.form.message.error';
    public static $unavailableError = 'booking.new.error.unavailable';
    public static $credentialError = 'user.form.credential.error';
    public static $messageDeliveryInvalid = 'booking.new.delivery.error';
    public static $messageDeliveryMaxInvalid = 'booking.new.delivery_max.error';

    private $bookingManager;
    private $dispatcher;
    private $allowSingleDay;
    private $endDayIncluded;
    private $minStartTimeDelay;
    private $acceptationDelay;
    private $addressDelivery;

    /**
     * @param BookingManager           $bookingManager
     * @param EventDispatcherInterface $dispatcher
     * @param array                    $parameters
     */
    public function __construct(
        BookingManager $bookingManager,
        EventDispatcherInterface $dispatcher,
        $parameters
    ) {
        $this->bookingManager = $bookingManager;
        $this->dispatcher = $dispatcher;

        $parameters = $parameters["parameters"];
        $this->allowSingleDay = $parameters['cocorico_booking_allow_single_day'];
        $this->endDayIncluded = $parameters['cocorico_booking_end_day_included'];
        $this->minStartTimeDelay = $parameters['cocorico_booking_min_start_time_delay'];
        $this->acceptationDelay = $parameters['cocorico_booking_acceptation_delay'];
        $this->addressDelivery = $parameters['cocorico_user_address_delivery'];
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Booking $booking */
        $booking = $builder->getData();

        $builder
            ->add(
                'date_range',
                DateRangeType::class,
                array(
                    'mapped' => false,
                    /** @Ignore */
                    'label' => false,
                    'required' => true,
                    'start_options' => array(
                        'label' => 'booking.form.start',
                        'mapped' => true,
                        'data' => $booking->getStart(),
                    ),
                    'end_options' => array(
                        'label' => 'booking.form.end',
                        'mapped' => true,
                        'data' => $booking->getEnd(),
                    ),
                    'allow_single_day' => $this->allowSingleDay,
                    'end_day_included' => $this->endDayIncluded,
                    'error_bubbling' => false,
                )
            );



        /**
         * Message type
         */
        $builder
            ->add(
                'message',
                TextareaType::class,
                array(
                    'label' => 'booking.form.message',
                    'required' => true
                )
            );


        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();
                //Set Booking Amounts or throw Error if booking is invalid
                /** @var Booking $booking */
                $booking = $event->getData();
                $result = $this->bookingManager->checkBookingAndSetAmounts($booking);
                $booking = $result->booking;
                $errors = $result->errors;

                if (!count($errors)) {
                    $event->setData($booking);
                } else {
                    $this->formErrors($form, $errors, $booking->getUser()->getTimeZone());
                }
            }
        );

        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();

                $message = $form->get('message')->getData();
                if (empty($message)) {
                    $form['message']->addError(
                        new FormError(self::$messageError)
                    );
                }
            }
        );
    }
}