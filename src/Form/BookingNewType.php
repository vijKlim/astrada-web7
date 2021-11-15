<?php


namespace App\Form;


use App\Entity\Booking;
use App\Form\Type\DateRangeType;
use App\Service\BookingManager;
use DateInterval;
use DateTime;
use DateTimeZone;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

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
        $this->allowSingleDay = $parameters['astrada_booking_allow_single_day'];
        $this->endDayIncluded = $parameters['astrada_booking_end_day_included'];
        $this->minStartTimeDelay = $parameters['astrada_booking_min_start_time_delay'];
        $this->acceptationDelay = $parameters['astrada_booking_acceptation_delay'];
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

    /**
     * todo: decouple external bundles errors management
     *
     * Add errors to the form if any
     *
     * @param FormInterface $form
     * @param array         $errors
     * @param  string       $timezone
     */
    private function formErrors(FormInterface $form, $errors, $timezone)
    {
        $keys = array_keys($errors, 'date_range.invalid.min_start');
        if (count($keys)) {
            foreach ($keys as $key) {
                unset($errors[$key]);
            }
            $minStart = new DateTime();
            $minStart->setTimezone(new DateTimeZone($timezone));
            if ($this->minStartTimeDelay > 0) {
                $minStart->add(new DateInterval('PT'.$this->minStartTimeDelay.'M'));
                $minStart->setTime(0, 0, 0);
            }
            $form['date_range']->addError(
                new FormError(
                    'date_range.invalid.min_start {{ min_start_day }}',
                    'astrada',
                    array(
                        '{{ min_start_day }}' => $minStart->format('d/m/Y'),
                    )
                )
            );
        }

        $keys = array_keys($errors, 'date_range.invalid.acceptation');
        if (count($keys)) {
            foreach ($keys as $key) {
                unset($errors[$key]);
            }
            $maxAcceptableDate = new DateTime();
            $maxAcceptableDate->setTimezone(new DateTimeZone($timezone));
            $maxAcceptableDate->add(new DateInterval('PT'.$this->acceptationDelay.'M'));
            $maxAcceptableDate->add(new DateInterval('P1D'));
            $form['date_range']->addError(
                new FormError(
                    'date_range.invalid.min_start {{ min_start_day }}',
                    'astrada',
                    array(
                        '{{ min_start_day }}' => $maxAcceptableDate->format('d/m/Y'),
                    )
                )
            );
        }

        $keys = array_keys($errors, 'time_range.invalid.min_start');
        if (count($keys)) {
            foreach ($keys as $key) {
                unset($errors[$key]);
            }
            $minStart = new DateTime();
            $minStart->setTimezone(new DateTimeZone($timezone));
            if ($this->minStartTimeDelay > 0) {
                $minStart->add(new DateInterval('PT'.$this->minStartTimeDelay.'M'));
            }
            $form['date_range']->addError(
                new FormError(
                    'time_range.invalid.min_start {{ min_start_time }}',
                    'astrada',
                    array(
                        '{{ min_start_time }}' => $minStart->format('d/m/Y H:i'),
                    )
                )
            );
        }

        $keys = array_keys($errors, 'unavailable');
        if (count($keys)) {
            foreach ($keys as $key) {
                unset($errors[$key]);
            }
            $form['date_range']->addError(
                new FormError(self::$unavailableError)
            );
        }

        $keys = array_keys($errors, 'amount_invalid');
        if (count($keys)) {
            foreach ($keys as $key) {
                unset($errors[$key]);
            }
            $form['date_range']->addError(
                new FormError(
                    'booking.new.error.amount_invalid {{ min_price }}',
                    'astrada',
                    array(
                        '{{ min_price }}' => $this->bookingManager->minPrice / 100 . " " . $this->currencySymbol,
                    )
                )
            );
        }



        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $form['date_range']->addError(
                    new FormError($error)
                );
            }
        }
    }
}