<?php

namespace App\Sylius\Cart;
use App\Sylius\Order\OrderInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Order\Context\CartNotFoundException;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Order\Repository\OrderRepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class BusinessCartContext implements CartContextInterface
{
    private $session;

    private $orderRepository;

    private $orderFactory;

    private $sessionKeyName;

    /**
     * @var ChannelContextInterface
     */
    private ChannelContextInterface $channelContext;

    /**
     * @var AuthorizationCheckerInterface
     */
    private AuthorizationCheckerInterface $authorizationChecker;

    /** @var OrderInterface|null */
    private $cart;

    /**
     * @param SessionInterface $session
     * @param OrderRepositoryInterface $orderRepository
     * @param string $sessionKeyName
     */
    public function __construct(
        SessionInterface $session,
        OrderRepositoryInterface $orderRepository,
        FactoryInterface $orderFactory,
        string $sessionKeyName,
        ChannelContextInterface $channelContext,
        BusinessResolver $resolver,
        AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->session = $session;
        $this->orderRepository = $orderRepository;
        $this->orderFactory = $orderFactory;
        $this->sessionKeyName = $sessionKeyName;
        $this->channelContext = $channelContext;
        $this->resolver = $resolver;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function getCart(): BaseOrderInterface
    {
        if (null !== $this->cart) {
            return $this->cart;
        }

        $cart = null;

        if ($this->session->has($this->sessionKeyName)) {

            $cart = $this->orderRepository->findCartById($this->session->get($this->sessionKeyName));

            if (null === $cart || $cart->getChannel()->getCode() !== $this->channelContext->getChannel()->getCode()) {
                $this->session->remove($this->sessionKeyName);
            } else {
                try {
                    if (!$cart->getBusiness()->isEnabled()
                        && !$this->authorizationChecker->isGranted('edit', $cart->getBusiness())) {
                        $cart = null;
                        $this->session->remove($this->sessionKeyName);
                    }
                } catch (EntityNotFoundException $e) {
                    $cart = null;
                    $this->session->remove($this->sessionKeyName);
                }
            }

            // This happens when the user has a cart stored in session,
            // and is browsing another restaurant.
            // In this case, we want to show an empty cart to the user.
            if (null !== $cart) {
                if ($business = $this->resolver->resolve()) {
                    if (!$this->resolver->accept($cart)) {
                        $cart->clearItems();
                        $cart->setShippingTimeRange(null);
                        $cart->setBusiness($business);
                    }
                }
            }
        }

        if (null === $cart) {

            $business = $this->resolver->resolve();

            if (null === $business) {

                throw new CartNotFoundException('No business could be resolved from request.');
            }

            $cart = $this->orderFactory->createForBusiness($business);
        }

        $this->cart = $cart;

        return $cart;
    }
}