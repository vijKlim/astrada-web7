<?php


namespace App\Form;


use App\Entity\Booking;
use App\Entity\Model\DateRange;
use App\Entity\Model\DateTimeRange;
use App\Form\Type\DateRangeType;
use DateTime;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class BookingType
    extends AbstractType
{
    protected $dispatcher;
    protected $allowSingleDay;
    protected $endDayIncluded;
    protected $daysDisplayMode;
    protected $timesDisplayMode;
    protected $timeUnitIsDay;

    /**
     * @param EventDispatcherInterface $dispatcher
     * @param bool   $allowSingleDay
     * @param bool   $endDayIncluded
     * @param string $daysDisplayMode
     * @param string $timesDisplayMode
     * @param int    $timeUnit
     */
    public function __construct(
        $dispatcher,
        $allowSingleDay,
        $endDayIncluded,
        $daysDisplayMode,
        $timesDisplayMode,
        $timeUnit
    ) {
        $this->dispatcher = $dispatcher;
        $this->allowSingleDay = $allowSingleDay;
        $this->endDayIncluded = $endDayIncluded;
        $this->daysDisplayMode = $daysDisplayMode;
        $this->timesDisplayMode = $timesDisplayMode;
        $this->timeUnitIsDay = ($timeUnit % 1440 == 0) ? true : false;
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
                        'required' => true,
                    ),
                    'end_options' => array(
                        'label' => 'booking.form.end',
                        'mapped' => true,
                        'data' => $booking->getEnd(),
                        'required' => true,
                    ),
                    'allow_single_day' => $this->allowSingleDay,
                    'end_day_included' => $this->endDayIncluded,
                    'block_name' => 'date_range_ajax',
                    'display_mode' => $this->daysDisplayMode
                )

            );



        //Dispatch BOOKING_PRICE_FORM_BUILD Event. Listener listening this event can add fields and validation
//        $this->dispatcher->dispatch(
//            BookingFormEvents::BOOKING_PRICE_FORM_BUILD,
//            new BookingFormBuilderEvent($builder)
//        );

        //Sync date and time
        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                /** @var Booking $booking */
                $booking = $event->getData();
                $form = $event->getForm();

                /** @var DateRange $dateRange */
                $dateRange = clone $form->get('date_range')->getData();
                $booking->setStart($dateRange->getStart());
                $booking->setEnd($dateRange->getEnd());
                $booking->setStartTime(new DateTime('1970-01-01 00:00'));
                $booking->setEndTime(new DateTime('1970-01-01 00:00'));

                if (!$this->timeUnitIsDay) {
                    //Sync booking date and time from date and time range
                    $timeRange = clone $form->get('time_range')->getData();
                    $dateTimeRange = DateTimeRange::addTimesToDates($dateRange, $timeRange);
                    $booking->setDateRange($dateTimeRange->getDateRange());
                    $booking->setTimeRange($dateTimeRange->getFirstTimeRange());
                }

                $event->setData($booking);
            }
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => Booking::class,
                'csrf_token_id' => 'booking',
                'constraints' => new Valid(),
                'validation_groups' => array('new'),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'booking';
    }

}
