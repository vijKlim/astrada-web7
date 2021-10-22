<?php

namespace App\Controller;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Controller\Utils\UserTrait;
use App\Entity\Address;
use App\Form\AddressType;
use App\Form\UpdateProfileType;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Nucleos\UserBundle\Model\UserManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\PreAuthenticationJWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;
use Cocur\Slugify\SlugifyInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManagerInterface;
use phpcent\Client as CentrifugoClient;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Repository\OrderRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Exception\ExceptionInterface as RoutingException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProfileController extends AbstractController
{
    const ITEMS_PER_PAGE = 20;

    use UserTrait;



    public function indexAction(Request $request,
        SlugifyInterface $slugify,
        TranslatorInterface $translator,
        JWTEncoderInterface $jwtEncoder,
        IriConverterInterface $iriConverter,
        PaginatorInterface $paginator,
        EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();

        if ($user->hasRole('ROLE_COURIER')) {

            return $this->tasksAction($request);
        }

        $customer = $user->getCustomer();

        return $this->render('profile/index.html.twig', array(
            'user' => $user,
            'customer' => $customer,
        ));
    }

    /**
     * @Route("/profile/edit", name="profile_edit")
     */
    public function editProfileAction(Request $request, UserManagerInterface $userManager) {

        $user = $this->getUser();

        $editForm = $this->createForm(UpdateProfileType::class, $user);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            if ($editForm->getClickedButton() && 'loopeatDisconnect' === $editForm->getClickedButton()->getName()) {
                $user->getCustomer()->clearLoopEatCredentials();
            }

            $userManager->updateUser($user);

            return $this->redirectToRoute('nucleos_profile_profile_show');
        }

        return $this->render('profile/edit_profile.html.twig', array(
            'form' => $editForm->createView()
        ));
    }




    /**
     * @Route("/profile/addresses", name="profile_addresses")
     */
    public function addressesAction(Request $request)
    {
        return $this->render('profile/addresses.html.twig', array(
            'addresses' => $this->getUser()->getAddresses(),
        ));
    }

    /**
     * @Route("/profile/addresses/new", name="profile_address_new")
     */
    public function newAddressAction(Request $request)
    {
        $address = new Address();

        $form = $this->createForm(AddressType::class, $address, [
            'with_name' => true,
            'with_widget' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address = $form->getData();

            $this->getUser()->addAddress($address);

            $manager = $this->getDoctrine()->getManagerForClass(Address::class);
            $manager->persist($address);
            $manager->flush();

            return $this->redirectToRoute('profile_addresses');
        }

        return $this->render('profile/new_address.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/profile/tracking/{date}", name="profile_tracking")
     */
//    public function trackingAction($date, Request $request)
//    {
//        $date = new \DateTime($date);
//
//        return $this->userTracking($this->getUser(), $date);
//    }




    /**
     * @Route("/profile/jwt", methods={"GET"}, name="profile_jwt")
     */
    public function jwtAction(Request $request,
        JWTManagerInterface $jwtManager,
        CentrifugoClient $centrifugoClient)
    {
        $user = $this->getUser();

        if ($request->getSession()->has('_jwt')) {

            $jwt = $request->getSession()->get('_jwt');

            try {
                $token = new PreAuthenticationJWTUserToken($jwt);
                $jwtManager->decode($token);
            } catch (JWTDecodeFailureException $e) {
                if (JWTDecodeFailureException::EXPIRED_TOKEN === $e->getReason()) {
                    $request->getSession()->set('_jwt', $jwtManager->create($user));
                }
            }

        } else {
            $request->getSession()->set('_jwt', $jwtManager->create($user));
        }

        return new JsonResponse([
            'jwt' => $request->getSession()->get('_jwt'),
            'cent_ns'  => $this->getParameter('centrifugo_namespace'),
            'cent_usr' => $user->getUsername(),
            'cent_tok' => $centrifugoClient->generateConnectionToken($user->getUsername(), (time() + 3600)),
        ]);
    }

    /**
     * @Route("/profile/notifications", name="profile_notifications")
     */
//    public function notificationsAction(Request $request, TopBarNotifications $topBarNotifications, NormalizerInterface $normalizer)
//    {
//        $notifications = $topBarNotifications->getLastNotifications($this->getUser());
//
//        if ($request->query->has('format') && 'json' === $request->query->get('format')) {
//
//            return new JsonResponse([
//                'notifications' => $normalizer->normalize($notifications, 'json'),
//                'unread' => (int) $topBarNotifications->countNotifications($this->getUser())
//            ]);
//        }
//
//        return $this->render('profile/notifications.html.twig', [
//            'notifications' => $notifications
//        ]);
//    }

    /**
     * @Route("/profile/notifications/read", methods={"POST"}, name="profile_notifications_mark_as_read")
     */
//    public function markNotificationsAsReadAction(Request $request, TopBarNotifications $topBarNotifications)
//    {
//        $ids = [];
//        $content = $request->getContent();
//        if (!empty($content)) {
//            $ids = json_decode($content, true);
//        }
//
//        $topBarNotifications->markAsRead($this->getUser(), $ids);
//
//        return new Response('', 204);
//    }

    public function redirectToDashboardAction($path, Request $request, RouterInterface $router)
    {
        $dashboardPath = sprintf('/dashboard/%s', $path);

        try {

            $router->match($dashboardPath);

            $queryString = $request->getQueryString();

            return $this->redirect($dashboardPath . (!empty($queryString) ? sprintf('?%s', $queryString) : ''), 301);

        } catch (RoutingException $e) {}

        throw $this->createNotFoundException();
    }
}
