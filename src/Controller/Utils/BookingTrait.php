<?php


namespace App\Controller\Utils;


use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\Base\GeoCoordinates;
use App\Entity\Booking;
use App\Entity\LocalBusiness;
use App\Service\Routing\Google;
use App\Service\Routing\Osrm;
use Doctrine\Persistence\ObjectRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

trait BookingTrait
{
    /**
     * @HideSoftDeleted
     */
    public function businessBookingsAction($id, Request $request, IriConverterInterface $iriConverter, PaginatorInterface $paginator)
    {

        $business = $this->getDoctrine()
            ->getRepository(LocalBusiness::class)
            ->find($id);

        $this->accessControl($business);

        $bookings = $this->getBookings($business,  $request, $paginator);
        $routes = $request->attributes->get('routes');

        return $this->render($request->attributes->get('template'), $this->withRoutes([
            'layout' => $request->attributes->get('layout'),
            'bookings' => $bookings,
            'listingSubscriptions' => $this->getListingSubscriptions(),
            'business' => $business,
            'business_iri' => $iriConverter->getIriFromItem($business),
        ], $routes));
    }

    /**
     * @HideSoftDeleted
     */
    public function getBookings(LocalBusiness $business,  Request $request,  PaginatorInterface $paginator)
    {
        $qb = $this->getDoctrine()
            ->getRepository(Booking::class)
            ->createQueryBuilder('p');

        $qb->andWhere('p.business = :business');
        $qb->setParameter('business', $business);
        $qb->orderBy('p.newBookingAt', 'ASC');

        return $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            10,
            [
                PaginatorInterface::DEFAULT_SORT_FIELD_NAME => 'p.start',
                PaginatorInterface::DEFAULT_SORT_DIRECTION => 'asc',
            ]
        );


    }

    public function businessBookingAction($businessId, $bookingId, Request $request, Google $google)
    {
        $business = $this->getDoctrine()
            ->getRepository(LocalBusiness::class)
            ->find($businessId);

        $this->accessControl($business);

        /** @var Booking $booking */
        $booking  = $this->getDoctrine()
            ->getRepository(Booking::class)
            ->find($bookingId);


        $routes = $request->attributes->get('routes');


        return $this->render($request->attributes->get('template'), $this->withRoutes([
            'layout' => $request->attributes->get('layout'),
            'business' => $business,
            'booking' => $booking,
        ], $routes));
    }



}