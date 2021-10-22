<?php


namespace App\Controller;

use App\Annotation\HideSoftDeleted;
use App\Controller\Utils\AccessControlTrait;
use App\Controller\Utils\BusinessListingTrait;
use App\Controller\Utils\BusinessTrait;
use App\Entity\Listing;
use App\Entity\LocalBusiness;
use App\Entity\Sylius\Customer;
use App\Entity\User;
use App\Form\BannerType;
use App\Form\MaintenanceType;
use App\Form\SettingsType;
use App\Form\UpdateProfileType;
use Knp\Component\Pager\PaginatorInterface;
use App\Service\SettingsManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Nucleos\UserBundle\Model\UserManagerInterface;
use Nucleos\UserBundle\Util\TokenGeneratorInterface;
use Nucleos\UserBundle\Util\CanonicalizerInterface;
use GuzzleHttp\Client as HttpClient;
use phpcent\Client as CentrifugoClient;
use Redis;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;


class AdminController extends AbstractController
{
    use AccessControlTrait;
    use BusinessTrait;
    use BusinessListingTrait;

    protected function getBusinessRoutes()
    {
        return [
            'businesses' => 'admin_businesses',
            'business' => 'admin_business',
            'service_taxon' => 'admin_business_service_taxon',
            'service_taxons' => 'admin_business_service_taxons',
            'products' => 'admin_business_products',
            'product_options' => 'admin_business_product_options',
            'product_new' => 'admin_business_product_new',
            'listings' => 'admin_business_listings',
            'listing_new' => 'admin_business_listing_new',
            'dashboard' => 'admin_business_dashboard',
            'planning' => 'admin_business_planning',
            'stripe_oauth_redirect' => 'admin_business_stripe_oauth_redirect',
            'stats' => 'admin_business_stats',
            'deposit_refund' => 'admin_business_deposit_refund',
            'promotions' => 'admin_business_promotions',
            'promotion_new' => 'admin_business_new_promotion',
            'promotion' => 'admin_business_promotion',
            'product_option_preview' => 'admin_business_product_option_preview',
        ];
    }

    protected function getListingRoutes()
    {
        return [
            'listings' => 'admin_listings',
            'listing' => 'admin_business_listing',
        ];
    }

    public function __construct(
        TranslatorInterface $translator,
        HttpClient $browserlessClient
    )
    {
        $this->translator = $translator;
        $this->browserlessClient = $browserlessClient;
    }

    const ITEMS_PER_PAGE = 20;


    /**
     * @Route("/admin", name="admin_index")
     */
    public function indexAction()
    {
        $response = new Response();

        $response->setContent('<html><body><h1>Admin Dashboard</h1></body></html>');
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/html');

        $response->send();
    }


    public function usersAction(Request $request, PaginatorInterface $paginator)
    {
        $qb = $this->getDoctrine()
            ->getRepository(Customer::class)
            ->createQueryBuilder('c');

        $qb->leftJoin(User::class, 'u', Expr\Join::WITH, 'c.id = u.customer');

        $customers = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            self::ITEMS_PER_PAGE,
            [
                PaginatorInterface::DEFAULT_SORT_FIELD_NAME => 'c.id',
                PaginatorInterface::DEFAULT_SORT_DIRECTION => 'desc',
                PaginatorInterface::SORT_FIELD_ALLOW_LIST => ['u.username', 'c.id'],
                PaginatorInterface::FILTER_FIELD_ALLOW_LIST => ['u.roles', 'u.username']
            ]
        );

        $attributes = [];

//        foreach ($customers as $customer) {
//            $key = $customer->getEmailCanonical();
//
//            $qb = $this->orderRepository->createQueryBuilder('o');
//            $qb->andWhere('o.customer = :customer');
//            $qb->andWhere('o.state != :state');
//            $qb->setParameter('customer', $customer);
//            $qb->setParameter('state', OrderInterface::STATE_CART);
//
//            $res = $qb->getQuery()->getResult();
//
//            $attributes[$key]['orders_count'] = count($res);
//
//            $qb = $this->orderRepository->createQueryBuilder('o');
//            $qb->andWhere('o.customer = :customer');
//            $qb->andWhere('o.state != :state');
//            $qb->setParameter('customer', $customer);
//            $qb->setParameter('state', OrderInterface::STATE_CART);
//            $qb->orderBy('o.updatedAt', 'DESC');
//            $qb->setMaxResults(1);
//
//            $res = $qb->getQuery()->getOneOrNullResult();
//
//            $attributes[$key]['last_order'] = $res;
//        }

        return $this->render('admin/users.html.twig', array(
            'customers' => $customers,
            'attributes' => $attributes,
        ));
    }

    /**
     * @Route("/admin/user/{username}/edit", name="admin_user_edit")
     */
    public function userEditAction($username, Request $request, UserManagerInterface $userManager)
    {
        $user = $userManager->findUserByUsername($username);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        // Roles that can be edited by admin
        $editableRoles = ['ROLE_ADMIN', 'ROLE_BUSINESS', 'ROLE_STORE'];

        $originalRoles = array_filter($user->getRoles(), function ($role) use ($editableRoles) {
            return in_array($role, $editableRoles);
        });

        $editForm = $this->createForm(UpdateProfileType::class, $user, [
            'with_businesses' => true,
            'with_stores' => true,
            'with_roles' => true,
            'editable_roles' => $editableRoles
        ]);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $user = $editForm->getData();

            $roles = $editForm->get('roles')->getData();

            $rolesToRemove = array_diff($originalRoles, $roles);

            foreach ($rolesToRemove as $role) {
                $user->removeRole($role);
            }

            foreach ($roles as $role) {
                if (!$user->hasRole($role)) {
                    $user->addRole($role);
                }
            }

            $userManager->updateUser($user);

            $this->addFlash(
                'notice',
                $this->translator->trans('global.changesSaved')
            );

            return $this->redirectToRoute('admin_user_edit', ['username' => $user->getUsername()]);
        }

        return $this->render('admin/user_edit.html.twig', [
            'form' => $editForm->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route("/admin/user/{username}", name="admin_user_details")
     */
    public function userAction($username, Request $request, UserManagerInterface $userManager)
    {
        $user = $userManager->findUserByUsername($username);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        return $this->render('admin/user.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/admin/users/search", name="admin_users_search")
     */
    public function searchUsersAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(User::class);

        $results = $repository->search($request->query->get('q'));

        if ($request->query->has('format') && 'json' === $request->query->get('format')) {
            $data = array_map(function (User $user) {
                $text = sprintf('%s (%s)', $user->getEmail(), $user->getUsername());

                return [
                    'id' => $user->getId(),
                    'name' => $text,
                    'email' => $user->getEmail(),
                    'username' => $user->getUsername(),
                    'firstName' => $user->getGivenName(),
                    'lastName' => $user->getFamilyName(),
                ];
            }, $results);

            return new JsonResponse($data);
        }
    }

    /**
     * @Route("/admin/users/invite", name="admin_users_invite")
     */
    public function inviteUserAction(
        Request $request,
//        EmailManager $emailManager,
        TokenGeneratorInterface $tokenGenerator,
        EntityManagerInterface $objectManager,
        CanonicalizerInterface $canonicalizer
    )
    {
        die('soon...');
//        $form = $this->createForm(InviteUserType::class);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $invitation = $form->getData();
//
//            $roles = $form->get('roles')->getData();
//            $restaurants = $form->get('restaurants')->getData();
//            $stores = $form->get('stores')->getData();
//
//            foreach ($roles as $role) {
//                $invitation->addRole($role);
//            }
//
//            foreach ($restaurants as $restaurant) {
//                $invitation->addRestaurant($restaurant);
//                $invitation->addRole('ROLE_RESTAURANT');
//            }
//
//            foreach ($stores as $store) {
//                $invitation->addStore($store);
//                $invitation->addRole('ROLE_STORE');
//            }
//
//            // TODO Check if already invited
//            // TODO Check if same email already exists
//
//            $invitation->setEmail($canonicalizer->canonicalize($invitation->getEmail()));
//            $invitation->setUser($this->getUser());
//            $invitation->setCode($tokenGenerator->generateToken());
//
//            $objectManager->persist($invitation);
//            $objectManager->flush();
//
//            // Send invitation email
//            $message = $emailManager->createInvitationMessage($invitation);
//            $emailManager->sendTo($message, $invitation->getEmail());
//            $invitation->setSentAt(new \DateTime());
//
//            $objectManager->flush();
//
//            $this->addFlash(
//                'notice',
//                $this->translator->trans('basics.send_invitation.confirm')
//            );
//
//            return $this->redirectToRoute('admin_users');
//        }
//
//        return $this->render('admin/user_invite.html.twig', [
//            'form' => $form->createView(),
//        ]);
    }

    /**
     * @Route("/admin/promotions", name="admin_promotions")
     */
    public function promotionsAction()
    {
        die('soon...');
//        $qb = $this->promotionCouponRepository->createQueryBuilder('c');
//        $qb->andWhere('c.expiresAt IS NULL OR c.expiresAt > :date');
//        $qb->setParameter('date', new \DateTime());
//        $promotionCoupons = $qb->getQuery()->getResult();
//        return $this->render('admin/promotions.html.twig', [
//            'promotion_coupons' => $promotionCoupons,
//        ]);
    }


    public function settingsAction(Request $request, SettingsManager $settingsManager, Redis $redis)
    {

        /* Maintenance */

        $maintenanceForm = $this->createForm(MaintenanceType::class);

        $maintenanceForm->handleRequest($request);
        if ($maintenanceForm->isSubmitted() && $maintenanceForm->isValid()) {
            if ($maintenanceForm->getClickedButton()) {
                if ('enable' === $maintenanceForm->getClickedButton()->getName()) {
                    $maintenanceMessage = $maintenanceForm->get('message')->getData();

                    $redis->set('maintenance_message', $maintenanceMessage);
                    $redis->set('maintenance', '1');
                }
                if ('disable' === $maintenanceForm->getClickedButton()->getName()) {
                    $redis->del('maintenance_message');
                    $redis->del('maintenance');
                }
            }

            return $this->redirectToRoute('admin_settings');
        }

        /* Banner */

        $bannerForm = $this->createForm(BannerType::class);

        $bannerForm->handleRequest($request);
        if ($bannerForm->isSubmitted() && $bannerForm->isValid()) {
            if ($bannerForm->getClickedButton()) {
                if ('enable' === $bannerForm->getClickedButton()->getName()) {
                    $bannerMessage = $bannerForm->get('message')->getData();

                    $redis->set('banner_message', $bannerMessage);
                    $redis->set('banner', '1');
                }
                if ('disable' === $bannerForm->getClickedButton()->getName()) {
                    $redis->del('banner_message');
                    $redis->del('banner');
                }
            }

            return $this->redirectToRoute('admin_settings');
        }

        /* Settings */

        $settings = $settingsManager->asEntity();
        $form = $this->createForm(SettingsType::class, $settings);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            foreach ($data as $name => $value) {
                $settingsManager->set($name, $value);
            }

            $settingsManager->flush();

            $this->addFlash(
                'notice',
                $this->translator->trans('global.changesSaved')
            );

            return $this->redirectToRoute('admin_settings');
        }

        return $this->render('admin/settings.html.twig', [
            'timezone' => ini_get('date.timezone'),
            'form' => $form->createView(),
            'maintenance_form' => $maintenanceForm->createView(),
            'maintenance' => $redis->get('maintenance'),
            'banner_form' => $bannerForm->createView(),
            'banner' => $redis->get('banner'),
        ]);
    }

    /**
     * @HideSoftDeleted
     */
    public function businessListAction(Request $request, SettingsManager $settingsManager)
    {
        $routes = $request->attributes->get('routes');


        [ $businesses, $pages, $page ] = $this->getBusinessList($request);

        return $this->render($request->attributes->get('template'), [
            'layout' => $request->attributes->get('layout'),
            'businesses' => $businesses,
            'pages' => $pages,
            'page' => $page,
            'dashboard_route' => $routes['dashboard'],
            'business_route' => $routes['business'],
            'service_taxons_route' => $routes['service_taxons'],
            'products_route' => $routes['products'],
            'listings_route' => $routes['listings'],
        ]);
    }

    /**
     * @Route("/admin/businesses/search", name="admin_businesses_search")
     */
    public function searchBusinessesAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(LocalBusiness::class);

        $results = $repository->search($request->query->get('q'));

        if ($request->query->has('format') && 'json' === $request->query->get('format')) {
            $data = array_map(function (LocalBusiness $business) {
                return [
                    'id' => $business->getId(),
                    'name' => $business->getName(),
                ];
            }, $results);

            return new JsonResponse($data);
        }
    }


    public function customizeAction(Request $request)
    {
        $isDemo = $this->getParameter('is_demo');

        if ($isDemo) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(CustomizeType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash(
                'notice',
                $this->translator->trans('global.changesSaved')
            );

            return $this->redirectToRoute('admin_customize');
        }

        return $this->render('admin/customize.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    protected function getBusinessList(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(LocalBusiness::class);

        $countAll = $repository
            ->createQueryBuilder('r')->select('COUNT(r)')
            ->getQuery()->getSingleScalarResult();

        $pages = ceil($countAll / self::ITEMS_PER_PAGE);
        $page = $request->query->getInt('p', 1);

        $offset = self::ITEMS_PER_PAGE * ($page - 1);

        $businesses = $repository->findBy([], [
            'enabled' => 'DESC',
            'id' => 'DESC',
        ], self::ITEMS_PER_PAGE, $offset);

        return [ $businesses, $pages, $page ];
    }


    /**
     * @HideSoftDeleted
     */
    public function listingListAction(Request $request, SettingsManager $settingsManager)
    {
        $routes = $request->attributes->get('routes');


        [ $listings, $pages, $page ] = $this->getListingList($request);

        return $this->render($request->attributes->get('template'), [
            'layout' => $request->attributes->get('layout'),
            'listings' => $listings,
            'pages' => $pages,
            'page' => $page,
            'dashboard_route' => $routes['dashboard'],
            'listing_route' => $routes['listing'],
            'products_route' => $routes['products'],
        ]);
    }

    protected function getListingList(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(Listing::class);

        $countAll = $repository
            ->createQueryBuilder('r')->select('COUNT(r)')
            ->getQuery()->getSingleScalarResult();

        $pages = ceil($countAll / self::ITEMS_PER_PAGE);
        $page = $request->query->getInt('p', 1);

        $offset = self::ITEMS_PER_PAGE * ($page - 1);

        $listings = $repository->findBy([], [
            'certified' => 'DESC',
            'id' => 'DESC',
        ], self::ITEMS_PER_PAGE, $offset);

        return [ $listings, $pages, $page ];
    }
}