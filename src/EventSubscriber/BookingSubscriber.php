<?php


namespace App\EventSubscriber;


use App\Event\BookingEvent;
use App\Event\BookingEvents;
use App\Factory\TopicFactory;
use App\Service\BookingManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BookingSubscriber
    implements EventSubscriberInterface
{
    protected $topicFactory;
    protected $bookingManager;
    protected $dispatcher;

    /**
     * @param TopicFactory $topicFactory
     */
    public function __construct(TopicFactory $topicFactory,BookingManager $bookingManager, EventDispatcherInterface $dispatcher)
    {
        $this->topicFactory = $topicFactory;
        $this->bookingManager = $bookingManager;
        $this->dispatcher = $dispatcher;
    }


    /**
     * Create a new booking
     *
     * @param BookingEvent $event
     */
    public function onBookingNewSubmitted(BookingEvent $event)
    {
        $booking = $this->bookingManager->create($event->getBooking());
        if ($booking) {
            $event->setBooking($booking);
            $this->dispatcher->dispatch( $event,BookingEvents::BOOKING_NEW_CREATED);
        }
    }
    public function onBookingNewCreated(BookingEvent $event)
    {
        $booking = $event->getBooking();
        $this->topicFactory->createForBooking($booking);
    }


    public static function getSubscribedEvents()
    {
        return array(
            BookingEvents::BOOKING_NEW_SUBMITTED => array('onBookingNewSubmitted', 0),
            BookingEvents::BOOKING_NEW_CREATED => array('onBookingNewCreated', 1),
        );
    }

}