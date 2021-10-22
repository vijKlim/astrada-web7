<?php

namespace App\EventSubscriber;

use App\Entity\LocalBusiness;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProfileSubscriber implements EventSubscriberInterface
{
    private $tokenStorage;
    private $urlGenerator;

    private static $blacklist = [
        'profile_notifications',
        'profile_jwt',
    ];

    public function __construct(TokenStorageInterface $tokenStorage, UrlGeneratorInterface $urlGenerator)
    {
        $this->tokenStorage = $tokenStorage;
        $this->urlGenerator = $urlGenerator;
    }

    private function findResourceInSession(Request $request, Collection $items, $sessionKey)
    {
        if (count($items) === 0) {
            return;
        }

        if ($request->getSession()->has($sessionKey)) {
            foreach ($items as $item) {
                if ($item->getId() === $request->getSession()->get($sessionKey)) {
                    $request->attributes->set($sessionKey, $item);
                    return;
                }
            }
            // There is something in session, but we couldn't find it
            $request->getSession()->remove($sessionKey);
        }

        $item = $items->first();
        $request->getSession()->set($sessionKey, $item->getId());
        $request->attributes->set($sessionKey, $item);
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->has('_route')) {
            return;
        }

        $route = $request->attributes->get('_route');

        if (in_array($route, self::$blacklist)) {
            return;
        }

        // Skip if this is an API request
        if ($request->attributes->has('_api_resource_class')) {
            return;
        }

        if (!$request->hasPreviousSession()) {

            return;
        }

        if (null === $token = $this->tokenStorage->getToken()) {

            return;
        }

        if (!is_object($user = $token->getUser())) {

            return; // e.g. anonymous authentication
        }

        if (!$user->hasRole('ROLE_BUSINESS')) {

            return;
        }


        $businesses = $user->getBusinesses();

        if (0 === count($businesses)) {

            return;
        }

        if ($route === 'dashboard' &&  $request->query->has('business')) {

            if ($request->query->has('business')) {
                foreach ($businesses as $business) {
                    if ($business->getId() === $request->query->getInt('business')) {
                        $request->getSession()->set('_business', $business->getId());
                        $event->setResponse(
                            new RedirectResponse($this->urlGenerator->generate('dashboard'))
                        );
                        return;
                    }
                }
            }
        }

        if ($request->getSession()->has('_business')) {
            $this->findResourceInSession($request, $businesses, '_business');
        } else {
            if (count($businesses)) {
                $this->findResourceInSession($request, $businesses, '_business');
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => 'onKernelRequest',
        );
    }
}
