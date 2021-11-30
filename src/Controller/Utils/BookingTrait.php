<?php


namespace App\Controller\Utils;


use ApiPlatform\Core\Api\IriConverterInterface;
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

        $listings = $this->getListings($business,  $request, $paginator);
        $routes = $request->attributes->get('routes');

        return $this->render($request->attributes->get('template'), $this->withRoutes([
            'layout' => $request->attributes->get('layout'),
            'listings' => $listings,
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
            ->getRepository(Listing::class)
            ->createQueryBuilder('p');

        $qb->innerJoin(ListingTranslation::class, 't', Expr\Join::WITH, 't.translatable = p.id');
        $qb->andWhere('p.business = :business');
        $qb->setParameter('business', $business);
        $qb->orderBy('p.expirationDate', 'ASC');

        return $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            10,
            [
                PaginatorInterface::DEFAULT_SORT_FIELD_NAME => 't.title',
                PaginatorInterface::DEFAULT_SORT_DIRECTION => 'asc',
                PaginatorInterface::SORT_FIELD_ALLOW_LIST => ['t.title'],
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