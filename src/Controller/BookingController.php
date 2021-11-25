<?php


namespace App\Controller;


use App\Entity\Address;
use App\Entity\Base\GeoCoordinates;
use App\Entity\Booking;
use App\Entity\Listing;
use App\Entity\Model\ListingSearchRequest;
use App\Entity\Model\UserAddressRequest;
use App\Factory\TopicFactory;
use App\Form\BookingNewType;
use App\Form\BookingType;
use App\Form\Handler\BookingFormHandler;
use App\Service\BookingManager;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Booking controller.
 *
 * @Route("/booking")
 */
class BookingController extends AbstractController
{
    /**
     * @var BookingManager
     */
    private $bookingManager;
    private $listingSearchRequest;
    private $userAddressRequest;

    private $dispatcher;

    private $translator;


    public function __construct(
        BookingManager $bookingManager,
        ListingSearchRequest $listingSearchRequest,
        UserAddressRequest $userAddressRequest,
        EventDispatcherInterface $dispatcher,
        TranslatorInterface $translator)
    {
        $this->bookingManager = $bookingManager;
        $this->listingSearchRequest = $listingSearchRequest;
        $this->userAddressRequest = $userAddressRequest;
        $this->dispatcher = $dispatcher;
        $this->translator = $translator;
    }

    /**
     * Creates a new Booking form.
     *
     * @param  Listing $listing
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function bookingFormAction(Listing $listing)
    {
        $booking = $this->bookingManager->initBooking($listing,
            $this->getUser(),
            $this->listingSearchRequest->getDateTimeRange(),  $this->getLastUserSearchAddress());

        $form = $this->createBookingForm($booking);

        return $this->render(
            'booking/form.html.twig',
            array(
                'form' => $form->createView(),
                'booking' => $booking
            )
        );
    }

    /**
     * Get Booking
     *
     * @Route("/{listing_id}", name="listing_booking", requirements={"listing_id" = "\d+"})
     *
     * @Security("is_granted('ROLE_USER')")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function getBookingAction($listing_id, Request $request)
    {
        $listing = $this->getDoctrine()
            ->getRepository(Listing::class)->find($listing_id);

        if (!$listing) {
            throw new NotFoundHttpException();
        }

        $booking = $this->bookingManager->initBooking($listing,
            $this->getUser(),
            $this->listingSearchRequest->getDateTimeRange(), $this->getLastUserSearchAddress());

        $form = $this->createBookingForm($booking);
        $form->handleRequest($request);

        return $this->redirect(
            $this->generateUrl(
                'listing_booking_new',
                array(
                    'listing_id' => $listing->getId(),
                    'start' => $booking->getStart()->format('Y-m-d-H:i'),
                    'end' => $booking->getEnd()->format('Y-m-d-H:i'),
                )
            )
        );
    }

    /**
     * Creates a form for Booking Price.
     *
     * @param Booking $booking The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createBookingForm(Booking $booking)
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            BookingType::class,
            $booking,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl(
                    'listing_booking',
                    array(
                        'listing_id' => $booking->getListing()->getId()
                    )
                )
            )
        );

        return $form;
    }

    /**
     * Creates a new Booking entity.
     *
     * @Route("/{listing_id}/{start}/{end}/new",
     *      name="listing_booking_new",
     *      requirements={
     *          "listing_id" = "\d+"
     *      },
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(
        $listing_id,
        $start,
        $end,
        Request $request,
        TopicFactory $topicFactory,
        BookingFormHandler $bookingHandler
    ) {
        $listing = $this->getDoctrine()
            ->getRepository(Listing::class)->find($listing_id);

        if (!$listing) {
            throw new NotFoundHttpException();
        }
        $userAddress = $this->getLastUserSearchAddress();
        $booking = $bookingHandler->init($this->getUser(), $listing, new \DateTime($start), new \DateTime($end), $userAddress);


        //Availability is validated through BookingValidator and amounts are setted through Form Event PRE_SET_DATA
        $form = $this->createCreateForm($booking);

        $success = $bookingHandler->process($form);
        if ($success === 1) {//Success

            $topic = $topicFactory->createForBooking($booking);
            $booking->setTopic($topic);
            $booking = $this->bookingManager->create($booking);

            if ($booking) {

                //New Booking confirmation
                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->translator->trans('booking.new.success')
                );

                $response = new RedirectResponse(
                    $this->generateUrl(
                        'cocorico_dashboard_booking_show_asker',
                        array('id' => $booking->getId())
                    )
                );
            } else {
                throw new \Exception('booking.new.form.error');
            }

            return $response;
        } else {
            //$this->addFormMessagesToFlashBag($success);
        }



        return $this->render(
            'booking/new.html.twig',
            array(
                'booking' => $booking,
                'form' => $form->createView(),
                //Used to hide errors fields message when a secondary submission (Voucher, Delivery, ...) is done successfully
                'display_errors' => ($success < 2)
            )
        );
    }

    /**
     * Creates a form to create a Booking entity.
     *
     * @param Booking $booking The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Booking $booking)
    {
        $form = $this->get('form.factory')->createNamed(
            'booking',
            BookingNewType::class,
            $booking,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl(
                    'listing_booking_new',
                    array(
                        'listing_id' => $booking->getListing()->getId(),
                        'start' => $booking->getStart()->format('Y-m-d-H:i'),
                        'end' => $booking->getEnd()->format('Y-m-d-H:i'),
                    )
                ),
            )
        );

        return $form;
    }

    /**
     * @return Address
     */
    protected function getLastUserSearchAddress()
    {
        $userAddressRequest = $this->getUserAddressRequest();
        return $userAddressRequest->getAddress();
    }

    /**
     * @return UserAddressRequest
     */
    protected function getUserAddressRequest()
    {
        $session = $this->get('session');
        /** @var UserAddressRequest $userAddressRequest */
        $userAddressRequest = $session->has('user_address_request') ?
            $session->get('user_address_request') :
            $this->userAddressRequest;

        return $userAddressRequest;
    }
}