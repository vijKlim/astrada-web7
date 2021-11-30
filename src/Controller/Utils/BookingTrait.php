<?php


namespace App\Controller\Utils;


use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\Booking;
use App\Entity\LocalBusiness;
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
        $qb->orderBy('p.expirationDate', 'ASC');

        return $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            10,
            [
                PaginatorInterface::DEFAULT_SORT_FIELD_NAME => 'b.start',
                PaginatorInterface::DEFAULT_SORT_DIRECTION => 'asc',
            ]
        );


    }

    public function businessBookingAction($businessId, $listingId, Request $request,
                                          ObjectRepository $listingRepository,
                                          EntityManagerInterface $entityManager,
                                          EventDispatcherInterface $dispatcher)
    {
        $business = $this->getDoctrine()
            ->getRepository(LocalBusiness::class)
            ->find($businessId);

        $this->accessControl($business);

        $listing = $listingRepository
            ->find($listingId);

        $form =
            $this->createBusinessListingForm($business, $listing);

        $routes = $request->attributes->get('routes');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $listing = $form->getData();


            if ($form->getClickedButton()) {
                if ('delete' === $form->getClickedButton()->getName()) {
                    $entityManager->remove($listing);
                }
            }

            $entityManager->flush();

            $dispatcher->dispatch(new GenericEvent($business), 'catalog.updated');

            return $this->redirectToRoute($routes['listings'], ['id' => $businessId]);
        }

        return $this->render($request->attributes->get('template'), $this->withRoutes([
            'layout' => $request->attributes->get('layout'),
            'business' => $business,
            'listing' => $listing,
            'form' => $form->createView()
        ], $routes));
    }



}