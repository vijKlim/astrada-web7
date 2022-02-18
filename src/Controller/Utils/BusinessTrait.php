<?php


namespace App\Controller\Utils;


use ApiPlatform\Core\Api\IriConverterInterface;
use App\Annotation\HideSoftDeleted;
use App\Entity\LocalBusiness;
use App\Entity\Sylius\Product;
use App\Entity\Sylius\ProductTaxon;
use App\Entity\Sylius\TaxonRepository;
use App\Form\BusinessType;
use App\Form\ProductOptionType;
use App\Form\ProductType;
use App\Form\ServiceEditorType;
use App\Form\ServiceTaxonType;
use App\Sylius\Product\ProductInterface;
use App\Utils\ServiceEditor;
use App\Utils\ValidationUtils;
use Cocur\Slugify\SlugifyInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ObjectRepository;
use Knp\Component\Pager\PaginatorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Ramsey\Uuid\Uuid;
use Sylius\Component\Locale\Provider\LocaleProviderInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Product\Model\ProductTranslation;
use Sylius\Component\Product\Repository\ProductOptionRepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Vich\UploaderBundle\Handler\UploadHandler;
use Webmozart\Assert\Assert;

trait BusinessTrait
{
    abstract protected function getBusinessRoutes();

    protected function getBusinessRoute($name)
    {
        $routes = $this->getBusinessRoutes();

        return $routes[$name];
    }

    public function businessAction($id, Request $request,
                                     ValidatorInterface $validator,
                                     JWTEncoderInterface $jwtEncoder,
                                     IriConverterInterface $iriConverter,
                                     TranslatorInterface $translator)
    {
        $repository = $this->getDoctrine()->getRepository(LocalBusiness::class);

        $business = $repository->find($id);

        $this->accessControl($business);

        return $this->renderBusinessForm($business, $request, $validator, $jwtEncoder, $iriConverter, $translator);
    }

    public function newBusinessAction(Request $request,
                                        ValidatorInterface $validator,
                                        JWTEncoderInterface $jwtEncoder,
                                        IriConverterInterface $iriConverter,
                                        TranslatorInterface $translator)
    {
        // TODO Check roles
        $business = new LocalBusiness();

        return $this->renderBusinessForm($business, $request, $validator, $jwtEncoder, $iriConverter, $translator);
    }

    protected function renderBusinessForm(LocalBusiness $business, Request $request,
                                            ValidatorInterface $validator,
                                            JWTEncoderInterface $jwtEncoder,
                                            IriConverterInterface $iriConverter,
                                            TranslatorInterface $translator)
    {
        $form = $this->createForm(BusinessType::class, $business,
//            [
//            'loopeat_enabled' => $this->getParameter('loopeat_enabled'),
//            'edenred_enabled' => $this->getParameter('edenred_enabled'),
//            ]
        );

        // Associate Stripe account with business
//        if ($request->getSession()->getFlashBag()->has('stripe_account')) {
//            $messages = $request->getSession()->getFlashBag()->get('stripe_account');
//            if (!empty($messages)) {
//                foreach ($messages as $stripeAccountId) {
//                    $stripeAccount = $this->getDoctrine()
//                        ->getRepository(StripeAccount::class)
//                        ->find($stripeAccountId);
//                    if ($stripeAccount) {
//                        $business->addStripeAccount($stripeAccount);
//                        $this->getDoctrine()->getManagerForClass(LocalBusiness::class)->flush();
//
//                        $this->addFlash(
//                            'notice',
//                            $translator->trans('form.local_business.stripe_account.success')
//                        );
//                    }
//                }
//            }
//        }


        $activationErrors = [];
        $formErrors = [];
        $routes = $request->attributes->get('routes');

        $form->handleRequest($request);
        if ($form->isSubmitted()) {

            if ($form->isValid()) {
                $business = $form->getData();

                if ($form->getClickedButton() && 'delete' === $form->getClickedButton()->getName()) {

                    $this->getDoctrine()->getManagerForClass(LocalBusiness::class)->remove($business);
                    $this->getDoctrine()->getManagerForClass(LocalBusiness::class)->flush();

                    return $this->redirectToRoute($routes['businesses']);
                }

                if ($business->getId() === null && !$this->getUser()->hasRole('ROLE_ADMIN')) {
                    $this->getUser()->addBusiness($business);
                }

                // Make sure the business can be enabled, or disable it
                $violations = $validator->validate($business, null, ['activable']);
                if (count($violations) > 0) {
                    $business->setEnabled(false);
                }



                $this->getDoctrine()->getManagerForClass(LocalBusiness::class)->persist($business);
                $this->getDoctrine()->getManagerForClass(LocalBusiness::class)->flush();

                $this->addFlash(
                    'notice',
                    $translator->trans('global.changesSaved')
                );

                return $this->redirectToRoute($routes['success'], ['id' => $business->getId()]);
            } else {
                $violations = new ConstraintViolationList();
                foreach ($form->getErrors(true) as $error) {
                    $violations->add($error->getCause());
                }
                $formErrors = ValidationUtils::serializeValidationErrors($violations);
            }

        } else {
            $violations = $validator->validate($business, null, ['activable']);
            $activationErrors = ValidationUtils::serializeValidationErrors($violations);
        }

//        $zones = $this->getDoctrine()->getRepository(Zone::class)->findAll();
        $zoneNames = [];
//        foreach ($zones as $zone) {
//            array_push($zoneNames, $zone->getName());
//        }



        return $this->render($request->attributes->get('template'), $this->withRoutes([
            'zoneNames' => $zoneNames,
            'business' => $business,
            'activationErrors' => $activationErrors,
            'formErrors' => $formErrors,
            'form' => $form->createView(),
            'layout' => $request->attributes->get('layout'),
        ], $routes));
    }

    protected function withRoutes($params, array $routes = [])
    {
        $routes = array_merge($routes, $this->getBusinessRoutes());

        $routeParams = [];
        foreach ($routes as $key => $value) {
            $routeParams[sprintf('%s_route', $key)] = $value;
        }

        return array_merge($params, $routeParams);
    }

    public function businessDashboardAction($businessId, Request $request,
                                              EntityManagerInterface $entityManager,
                                              IriConverterInterface $iriConverter,
                                              AuthorizationCheckerInterface $authorizationChecker)
    {
//        $restaurant = $this->getDoctrine()
//            ->getRepository(LocalBusiness::class)
//            ->find($restaurantId);
//
//        $this->accessControl($restaurant);
//
//        $date = new \DateTime('now');
//        if ($request->query->has('date')) {
//            $date = new \DateTime($request->query->get('date'));
//        }
//
//        if ($request->query->has('order')) {
//            $order = $request->query->get('order');
//            if (is_numeric($order)) {
//
//                return $this->redirectToRoute($request->attributes->get('_route'), [
//                    'restaurantId' => $restaurant->getId(),
//                    'date' => $date->format('Y-m-d'),
//                    'order' => $iriConverter->getItemIriFromResourceClass(Order::class, [$order])
//                ], 301);
//            }
//        }
//
//        $start = clone $date;
//        $end = clone $date;
//
//        $start->setTime(0, 0, 0);
//        $end->setTime(23, 59, 59);
//
//        // FIXME
//        // Ideally, $authorizationChecker should be injected
//        // into OrderRepository directly, but it seems impossible with Sylius dependency injection
//        $orders = $entityManager->getRepository(Order::class)
//            ->findOrdersByRestaurantAndDateRange($restaurant, $start, $end, $authorizationChecker->isGranted('ROLE_ADMIN'));
//
//        $routes = $request->attributes->get('routes');
//
//        return $this->render($request->attributes->get('template'), $this->withRoutes([
//            'layout' => $request->attributes->get('layout'),
//            'restaurant' => $restaurant,
//            'restaurant_normalized' => $this->get('serializer')->normalize($restaurant, 'jsonld', [
//                'resource_class' => LocalBusiness::class,
//                'operation_type' => 'item',
//                'item_operation_name' => 'get',
//                'groups' => ['restaurant']
//            ]),
//            'orders_normalized' => $this->get('serializer')->normalize($orders, 'jsonld', [
//                'resource_class' => Order::class,
//                'operation_type' => 'item',
//                'item_operation_name' => 'get',
//                'groups' => ['order_minimal']
//            ]),
//            'initial_order' => $request->query->get('order'),
//            'routes' => $routes,
//            'date' => $date,
//        ], $routes));
    }


    public function businessServiceTaxonsAction($id, Request $request, FactoryInterface $taxonFactory)
    {
        $business = $this->getDoctrine()
            ->getRepository(LocalBusiness::class)
            ->find($id);

        $routes = $request->attributes->get('routes');
        $services = $business->getTaxons();

        $forms = [];
        foreach ($services as $service) {
            $forms[$service->getId()] = $this->createForm(ServiceTaxonType::class, $service)->createView();
        }

        $form = $this->createFormBuilder()
            ->add('name', TextType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $name = $form->get('name')->getData();

            $serviceTaxon = $taxonFactory->createNew();

            $uuid = Uuid::uuid1()->toString();

            $serviceTaxon->setCode($uuid);
            $serviceTaxon->setSlug($uuid);
            $serviceTaxon->setName($name);

            $business->addTaxon($serviceTaxon);

            $this->getDoctrine()->getManagerForClass(LocalBusiness::class)->flush();

            return $this->redirectToRoute($routes['service_taxon'], [
                'businessId' => $business->getId(),
                'serviceId' => $serviceTaxon->getId()
            ]);
        }

        return $this->render($request->attributes->get('template'), $this->withRoutes([
            'layout' => $request->attributes->get('layout'),
            'services' => $services,
            'business' => $business,
            'forms' => $forms,
            'form' => $form->createView(),
            'service_activate_route' => $routes['service_activate'],
        ], $routes));
    }

    public function activateBusinessServiceTaxonAction($businessId, $serviceId, Request $request,
                                                      TaxonRepository $taxonRepository,
                                                      TranslatorInterface $translator)
    {
        $business = $this->getDoctrine()
            ->getRepository(LocalBusiness::class)
            ->find($businessId);

        $this->accessControl($business);

        $serviceTaxon = $taxonRepository
            ->find($serviceId);

        $business->setServiceTaxon($serviceTaxon);

        $this->getDoctrine()->getManagerForClass(LocalBusiness::class)->flush();

        $this->addFlash(
            'notice',
            $translator->trans('business.services.activated', ['%service_name%' => $serviceTaxon->getName()])
        );

        $routes = $request->attributes->get('routes');

        return $this->redirectToRoute($routes['service_taxons'], [
            'id' => $business->getId(),
        ]);
    }


    public function deleteBusinessServiceTaxonChildAction($businessId, $serviceId, $sectionId, Request $request,
                                                         TaxonRepository $taxonRepository,
                                                         EntityManagerInterface $entityManager)
    {
        $business = $this->getDoctrine()
            ->getRepository(LocalBusiness::class)
            ->find($businessId);

        $this->accessControl($business);

        $serviceTaxon = $taxonRepository->find($serviceId);
        $toRemove = $taxonRepository->find($sectionId);

        $serviceTaxon->removeChild($toRemove);

        $entityManager->flush();

        $routes = $request->attributes->get('routes');

        return $this->redirectToRoute($routes['service_taxon'], [
            'businessId' => $business->getId(),
            'serviceId' => $serviceTaxon->getId()
        ]);
    }

    /**
     * @HideSoftDeleted
     */
    public function businessServiceTaxonAction($businessId, $serviceId, Request $request,
                                              TaxonRepository $taxonRepository,
                                              FactoryInterface $taxonFactory,
                                              EntityManagerInterface $entityManager,
                                              EventDispatcherInterface $dispatcher,
                                              TranslatorInterface $translator)
    {
        $routes = $request->attributes->get('routes');

        $business = $this->getDoctrine()
            ->getRepository(LocalBusiness::class)
            ->find($businessId);

        $this->accessControl($business);

        $serviceTaxon = $taxonRepository
            ->find($serviceId);

        $form = $this->createFormBuilder()
            ->add('name', TextType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $name = $form->get('name')->getData();

            $uuid = Uuid::uuid1()->toString();

            $child = $taxonFactory->createNew();
            $child->setCode($uuid);
            $child->setSlug($uuid);
            $child->setName($name);

            $serviceTaxon->addChild($child);
            $entityManager->flush();

            $this->addFlash(
                'notice',
                $translator->trans('global.changesSaved')
            );

            return $this->redirect($request->headers->get('referer'));
        }

        $serviceEditor = new ServiceEditor($business, $serviceTaxon);
        $serviceEditorForm = $this->createForm(ServiceEditorType::class, $serviceEditor);

        $originalTaxonProducts = new \SplObjectStorage();
        foreach ($serviceEditor->getChildren() as $child) {
            $taxonProducts = new ArrayCollection();
            foreach ($child->getTaxonProducts() as $taxonProduct) {
                $taxonProducts->add($taxonProduct);
            }

            $originalTaxonProducts[$child] = $taxonProducts;
        }

        // This will be used to determine if sections have been reordered
        $originalSectionPositions = [];
        foreach ($serviceEditor->getChildren() as $child) {
            $originalSectionPositions[$child->getPosition()] = $child->getId();
        }
        ksort($originalSectionPositions);
        $originalSectionPositions = array_values($originalSectionPositions);

        $serviceEditorForm->handleRequest($request);
        if ($serviceEditorForm->isSubmitted() && $serviceEditorForm->isValid()) {

            $serviceEditor = $serviceEditorForm->getData();

            $newSectionPositions = [];

            $em = $this->getDoctrine()->getManagerForClass(ProductTaxon::class);

            foreach ($serviceEditor->getChildren() as $child) {

                // The section is empty
                if (count($originalTaxonProducts[$child]) > 0 && count($child->getTaxonProducts()) === 0) {
                    foreach ($originalTaxonProducts[$child] as $originalTaxonProduct) {
                        $originalTaxonProducts[$child]->removeElement($originalTaxonProduct);
                        $em->remove($originalTaxonProduct);
                    }
                    continue;
                }

                $newSectionPositions[$child->getPosition()] = $child->getId();

                foreach ($child->getTaxonProducts() as $taxonProduct) {

                    $taxonProduct->setTaxon($child);

                    foreach ($originalTaxonProducts[$child] as $originalTaxonProduct) {
                        if (!$child->getTaxonProducts()->contains($originalTaxonProduct)) {
                            $child->getTaxonProducts()->removeElement($originalTaxonProduct);
                            $em->remove($originalTaxonProduct);
                        }
                    }
                }
            }

            ksort($newSectionPositions);
            $newSectionPositions = array_values($newSectionPositions);

            if ($originalSectionPositions !== $newSectionPositions) {
                $taxonRepository->reorder($serviceTaxon, 'position');
            }

            $entityManager->flush();

            if ($business->getServiceTaxon() === $serviceTaxon) {
                $dispatcher->dispatch(new GenericEvent($business), 'catalog.updated');
            }

            $this->addFlash(
                'notice',
                $translator->trans('global.changesSaved')
            );

            return $this->redirect($request->headers->get('referer'));
        }

        return $this->render($request->attributes->get('template'), $this->withRoutes([
            'layout' => $request->attributes->get('layout'),
            'business' => $business,
            'service' => $serviceTaxon,
            'form' => $form->createView(),
            'service_editor_form' => $serviceEditorForm->createView(),
        ], $routes));
    }


    /**
     * @HideSoftDeleted
     */
    public function businessProductsAction($id, Request $request, IriConverterInterface $iriConverter, PaginatorInterface $paginator)
    {
        $type = \App\Enum\Product::COMMON;

        $business = $this->getDoctrine()
            ->getRepository(LocalBusiness::class)
            ->find($id);

        $this->accessControl($business);

        $products = $this->getProducts($business, $type, $request, $paginator);

        $routes = $request->attributes->get('routes');

        return $this->render($request->attributes->get('template'), $this->withRoutes([
            'layout' => $request->attributes->get('layout'),
            'products' => $products,
            'business' => $business,
            'business_iri' => $iriConverter->getIriFromItem($business),
        ], $routes));
    }



    /**
     * @HideSoftDeleted
     */
    public function getProducts(LocalBusiness $business, $type, Request $request,  PaginatorInterface $paginator)
    {
        $qb = $this->getDoctrine()
            ->getRepository(Product::class)
            ->createQueryBuilder('p');

        $qb->innerJoin(ProductTranslation::class, 't', Expr\Join::WITH, 't.translatable = p.id');
        $qb->andWhere('p.business = :business');
        $qb->setParameter('business', $business);
        $qb->andWhere('p.type = :type');
        $qb->setParameter('type', $type);

        return $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            10,
            [
                PaginatorInterface::DEFAULT_SORT_FIELD_NAME => 't.name',
                PaginatorInterface::DEFAULT_SORT_DIRECTION => 'asc',
                PaginatorInterface::SORT_FIELD_ALLOW_LIST => ['t.name'],
            ]
        );


    }




    public function businessProductAction($businessId, $productId, Request $request,
                                            ObjectRepository $productRepository,
                                            EntityManagerInterface $entityManager,
                                            EventDispatcherInterface $dispatcher)
    {
        $business = $this->getDoctrine()
            ->getRepository(LocalBusiness::class)
            ->find($businessId);

        $this->accessControl($business);

        $product = $productRepository
            ->find($productId);

        $form =
            $this->createBusinessProductForm($business, $product);

        $routes = $request->attributes->get('routes');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $product = $form->getData();


            if ($form->getClickedButton()) {
                if ('delete' === $form->getClickedButton()->getName()) {
                    $entityManager->remove($product);
                }
            }

            $entityManager->flush();

            $dispatcher->dispatch(new GenericEvent($business), 'catalog.updated');

            return $this->redirectToRoute($routes['products'], ['id' => $businessId]);
        }

        return $this->render($request->attributes->get('template'), $this->withRoutes([
            'layout' => $request->attributes->get('layout'),
            'business' => $business,
            'product' => $product,
            'form' => $form->createView()
        ], $routes));
    }

    public function newBusinessProductAction($id, Request $request,
                                               FactoryInterface $productFactory,
                                               EntityManagerInterface $entityManager)
    {
        $business = $this->getDoctrine()
            ->getRepository(LocalBusiness::class)
            ->find($id);

        $this->accessControl($business);

        $product = $productFactory
            ->createNew();

        $product->setEnabled(false);

        $form =
            $this->createBusinessProductForm($business, $product);

        $routes = $request->attributes->get('routes');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $product = $form->getData();

            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute($routes['products'], ['id' => $id]);
        }

        return $this->render($request->attributes->get('template'), $this->withRoutes([
            'layout' => $request->attributes->get('layout'),
            'business' => $business,
            'product' => $product,
            'form' => $form->createView()
        ], $routes));
    }


    private function createBusinessProductForm(LocalBusiness $business, ProductInterface $product)
    {

//        $formClass = $product->getType() == \App\Enum\Product::WELL_DESIGN
//            ? WellDesignProductType::class : ProductType::class;

        $formClass = ProductType::class;

        return $this->createForm($formClass, $product, [
            'owner' => $business,
            'options_loader' => function (ProductInterface $product) use ($business) {

                $opts = [];
                foreach ($business->getProductOptions() as $opt) {
                    $opts[] = [
                        'product'  => $product,
                        'option'   => $opt,
                        'position' => $product->getPositionForOption($opt)
                    ];
                }

                uasort($opts, function ($a, $b) {
                    if ($a['position'] === $b['position']) return 0;
                    if ($a['position'] === -1) return 1;
                    if ($b['position'] === -1) return -1;
                    return $a['position'] < $b['position'] ? -1 : 1;
                });

                return $opts;
            }
        ]);
    }

    /**
     * @HideSoftDeleted
     */
    public function businessProductOptionsAction($id, Request $request)
    {
        $business = $this->getDoctrine()
            ->getRepository(LocalBusiness::class)
            ->find($id);

        $this->accessControl($business);

        $routes = $request->attributes->get('routes');

        return $this->render($request->attributes->get('template'), $this->withRoutes([
            'layout' => $request->attributes->get('layout'),
            'options' => $business->getProductOptions(),
            'business' => $business,
        ], $routes));
    }

    public function businessProductOptionAction($businessId, $optionId, Request $request,
                                                  ProductOptionRepositoryInterface $productOptionRepository,
                                                  EntityManagerInterface $entityManager,
                                                  TranslatorInterface $translator)
    {
        $filterCollection = $entityManager->getFilters();
        if ($filterCollection->isEnabled('disabled_filter')) {
            $filterCollection->disable('disabled_filter');
        }

        $business = $this->getDoctrine()
            ->getRepository(LocalBusiness::class)
            ->find($businessId);

        $this->accessControl($business);

        $productOption = $productOptionRepository
            ->find($optionId);

        $routes = $request->attributes->get('routes');

        $form = $this->createForm(ProductOptionType::class, $productOption);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $productOption = $form->getData();

            if ($form->getClickedButton() && 'delete' === $form->getClickedButton()->getName()) {

                $entityManager->remove($productOption);
                $entityManager->flush();

                return $this->redirectToRoute($routes['product_options'], ['id' => $businessId]);
            }

            foreach ($productOption->getValues() as $optionValue) {
                if (null === $optionValue->getCode()) {
                    $optionValue->setCode(Uuid::uuid4()->toString());
                }
            }

            $entityManager->flush();

            $this->addFlash(
                'notice',
                $translator->trans('global.changesSaved')
            );

            return $this->redirect($request->headers->get('referer'));
        }

        return $this->render($request->attributes->get('template'), $this->withRoutes([
            'layout' => $request->attributes->get('layout'),
            'business' => $business,
            'form' => $form->createView(),
        ], $routes));
    }

    public function businessProductOptionPreviewAction(Request $request,
                                                         FactoryInterface $productOptionFactory,
                                                         NormalizerInterface $serializer,
                                                         LocaleProviderInterface $localeProvider)
    {
        $productOption = $productOptionFactory
            ->createNew();

        $form = $this->createForm(ProductOptionType::class, $productOption);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $productOption = $form->getData();

            $enabledValues = $productOption->getValues()->filter(function ($value) {
                return $value->isEnabled();
            });

            $productOption->getValues()->clear();
            foreach ($enabledValues as $optionValue) {
                $productOption->getValues()->add($optionValue);
            }

            foreach ($productOption->getValues() as $optionValue) {
                // FIXME We shouldn't need to call setCurrentLocale
                $optionValue->setCurrentLocale($localeProvider->getDefaultLocaleCode());
                if (null === $optionValue->getCode()) {
                    $optionValue->setCode(Uuid::uuid4()->toString());
                }
            }

            return new JsonResponse(
                $serializer->normalize($productOption, 'json', ['groups' => ['product_option']])
            );
        }

        throw new BadRequestHttpException();
    }

    public function newBusinessProductOptionAction($id, Request $request,
                                                     FactoryInterface $productOptionFactory,
                                                     EntityManagerInterface $entityManager)
    {
        $business = $this->getDoctrine()
            ->getRepository(LocalBusiness::class)
            ->find($id);

        $this->accessControl($business);

        $productOption = $productOptionFactory
            ->createNew();

        $routes = $request->attributes->get('routes');

        $form = $this->createForm(ProductOptionType::class, $productOption);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $productOption = $form->getData();

            $productOption->setCode(Uuid::uuid4()->toString());
            foreach ($productOption->getValues() as $optionValue) {
                $optionValue->setCode(Uuid::uuid4()->toString());
            }
//            $productOption->setPosition(0);
            $business->addProductOption($productOption);

            $entityManager->flush();

            return $this->redirectToRoute($routes['product_options'], ['id' => $id]);
        }

        return $this->render($request->attributes->get('template'), $this->withRoutes([
            'layout' => $request->attributes->get('layout'),
            'business' => $business,
            'form' => $form->createView(),
        ], $routes));
    }
}