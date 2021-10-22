<?php


namespace App\Controller\Utils;


use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\Listing;
use App\Entity\ListingSubscription;
use App\Entity\ListingTranslation;
use App\Entity\LocalBusiness;
use App\Form\ListingType;
use App\Sylius\Product\ProductInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ObjectRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

trait BusinessListingTrait
{

    /**
     * @HideSoftDeleted
     */
    public function businessListingsAction($id, Request $request, IriConverterInterface $iriConverter, PaginatorInterface $paginator)
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
    public function getListings(LocalBusiness $business,  Request $request,  PaginatorInterface $paginator)
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

    public function businessListingAction($businessId, $listingId, Request $request,
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

    public function newBusinessListingAction($id,Request $request,
                                      FactoryInterface $listingFactory,
                                      FactoryInterface $productFactory,
                                      EntityManagerInterface $entityManager)
    {
        $business = $this->getDoctrine()
            ->getRepository(LocalBusiness::class)
            ->find($id);

        $this->accessControl($business);
        /** @var Listing $listing */
        $listing = $listingFactory
            ->createNew();


        $form =
            $this->createBusinessListingForm($business, $listing);

        $routes = $request->attributes->get('routes');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $listing = $form->getData();


            $entityManager->persist($listing);
            $entityManager->flush();

            return $this->redirectToRoute($routes['listings'], ['id' => $id]);
        }

        return $this->render($request->attributes->get('template'), $this->withRoutes([
            'layout' => $request->attributes->get('layout'),
            'business' => $business,
            'listing' => $listing,
            'form' => $form->createView()
        ], $routes));
    }

    private function createBusinessListingForm(LocalBusiness $business, Listing $listing)
    {
        return $this->createForm(ListingType::class, $listing, [
            'owner' => $business,
            'with_remember_address' => true,
        ]);
    }

    private function getListingSubscriptions()
    {
        $subscriptions = $this->getSubscriptionRepository()->findAll();
        $listingSubscriptions = [];
        /** @var ListingSubscription $subscription */
        foreach ($subscriptions as $subscription){
            $listingSubscriptions[$subscription->getProduct()->getId()] = $subscription;
        }
        return $listingSubscriptions;
    }
    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    private function getSubscriptionRepository()
    {
        return $this->getDoctrine()->getRepository(ListingSubscription::class);
    }

}