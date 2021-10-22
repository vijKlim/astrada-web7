<?php

namespace Astrada\SubscriptionBundle;

use Astrada\SubscriptionBundle\Model\SubscriptionInterface;
use Astrada\SubscriptionBundle\Model\SubscriptionProductInterface;
use Astrada\SubscriptionBundle\Repository\SubscriptionRepositoryInterface;
use Astrada\SubscriptionBundle\Event\SubscriptionEvent;
use Astrada\SubscriptionBundle\Event\SubscriptionEvents;
use Astrada\SubscriptionBundle\Exception\PermanentSubscriptionException;
use Astrada\SubscriptionBundle\Exception\ProductDefaultNotFoundException;
use Astrada\SubscriptionBundle\Exception\StrategyNotFoundException;
use Astrada\SubscriptionBundle\Exception\SubscriptionIntegrityException;
use Astrada\SubscriptionBundle\Exception\SubscriptionRenewalException;
use Astrada\SubscriptionBundle\Exception\SubscriptionStatusException;
use Astrada\SubscriptionBundle\Strategy\SubscriptionStrategyInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\User\UserInterface;


/**
 * Manages subscription workflow.
 *
 */
class SubscriptionManager
{
    /**
     * @var SubscriptionRegistry
     */
    private $registry;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var string
     */
    private $config;

    /**
     * Constructor.
     *
     * @param SubscriptionRegistry            $registry               Registry of strategies
     * @param SubscriptionRepositoryInterface $subscriptionRepository Subscription repository
     * @param EventDispatcherInterface        $eventDispatcher        Event Dispatcher
     * @param array                           $config                 Bundle configuration
     */
    public function __construct(
        SubscriptionRegistry $registry,
        SubscriptionRepositoryInterface $subscriptionRepository,
        EventDispatcherInterface $eventDispatcher,
        $config
    )
    {
        $this->registry               = $registry;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->eventDispatcher        = $eventDispatcher;
        $this->config                 = $config;
    }

    /**
     * Create a new subscription with a determinate strategy.
     *
     * @param SubscriptionProductInterface $product      Product that you want associate with subscription
     * @param UserInterface    $user         User to associate to subscription
     * @param string           $strategyName If you keep this null it will use product default strategy
     *
     * @return SubscriptionInterface
     *
     * @throws StrategyNotFoundException
     * @throws SubscriptionIntegrityException
     * @throws PermanentSubscriptionException
     */
    public function create(SubscriptionProductInterface $product, UserInterface $user, $strategyName = null)
    {
        // Get strategy
        $strategyName = $strategyName ?? $product->getStrategyCodeName();
        $strategy     = $this->registry->get($strategyName);

        // Get current enabled subscriptions of product
        $subscriptions = $this->subscriptionRepository->findByProduct($product, $user);

        // Check that subscriptions collection are a valid objects
        foreach ($subscriptions as $activeSubscription) {
            $this->checkSubscriptionIntegrity($activeSubscription);
        }

        $subscription = $strategy->createSubscription($product, $subscriptions);
        $subscription->setStrategy($strategyName);
        $subscription->setUser($user);

        return $subscription;
    }

    /**
     * Activate subscription.
     *
     * @param SubscriptionInterface $subscription
     * @param boolean               $isRenew
     *
     * @throws SubscriptionIntegrityException
     * @throws StrategyNotFoundException
     * @throws SubscriptionStatusException
     * @throws ProductDefaultNotFoundException
     */
    public function activate(SubscriptionInterface $subscription, $isRenew = false)
    {
        $this->checkSubscriptionIntegrity($subscription);
        $this->checkSubscriptionNonActive($subscription);

        $strategy     = $this->getStrategyFromSubscription($subscription);
        $finalProduct = $strategy->getProductStrategy()->getFinalProduct($subscription->getProduct());

        $subscription->setProduct($finalProduct);
        $subscription->activate();

        $subscriptionEvent = new SubscriptionEvent($subscription, $isRenew);
        $this->eventDispatcher->dispatch( $subscriptionEvent, SubscriptionEvents::ACTIVATE_SUBSCRIPTION);
    }

    /**
     * Renew subscription that has been expired.
     *
     * @param SubscriptionInterface $subscription
     *
     * @return SubscriptionInterface New
     *
     * @throws SubscriptionIntegrityException
     * @throws SubscriptionRenewalException
     * @throws ProductDefaultNotFoundException
     * @throws StrategyNotFoundException
     * @throws PermanentSubscriptionException
     * @throws SubscriptionStatusException
     */
    public function renew(SubscriptionInterface $subscription)
    {
        $this->checkSubscriptionIntegrity($subscription);
        $this->checkSubscriptionRenewable($subscription);
        $this->checkSubscriptionActive($subscription);

        // Expire the last subscription
        $this->expire($subscription, 'renew', true);

        // Get the next renewal product
        $renewalProduct = $this->getRenewalProduct($subscription->getProduct());
        $strategy       = $this->getStrategyFromSubscription($subscription);
        $finalProduct   = $strategy->getProductStrategy()->getFinalProduct($renewalProduct);

        // Create new subscription (following the way of expired subscription)
        $newSubscription = $this->create($finalProduct, $subscription->getUser(), $finalProduct->getStrategyCodeName());
        $newSubscription->setAutoRenewal(true);

        // Activate the next subscription
        $this->activate($newSubscription, true);

        $subscriptionEvent = new SubscriptionEvent($newSubscription);
        $this->eventDispatcher->dispatch( $subscriptionEvent, SubscriptionEvents::RENEW_SUBSCRIPTION);

        return $newSubscription;
    }

    /**
     * Get next roll product.
     *
     * @param SubscriptionProductInterface $product
     *
     * @return SubscriptionProductInterface
     */
    protected function getRenewalProduct(SubscriptionProductInterface $product)
    {
        if (null === $product->getNextRenewalProduct()) {
            return $product;
        }

        return $product->getNextRenewalProduct();
    }

    /**
     * Expire subscription.
     *
     * @param SubscriptionInterface $subscription
     * @param string                $reason The reason codename that you want set into the subscription
     * @param boolean               $isRenew
     */
    public function expire(SubscriptionInterface $subscription, $reason = 'expire', $isRenew = false)
    {
        $subscription->setReason($this->config['reasons'][$reason]);
        $subscription->deactivate();

        $subscriptionEvent = new SubscriptionEvent($subscription, $isRenew);
        $this->eventDispatcher->dispatch( $subscriptionEvent, SubscriptionEvents::EXPIRE_SUBSCRIPTION);
    }

    /**
     * Disable subscription.
     *
     * @param SubscriptionInterface $subscription
     */
    public function disable(SubscriptionInterface $subscription)
    {
        $subscription->setReason($this->config['reasons']['disable']);
        $subscription->deactivate();

        $subscriptionEvent = new SubscriptionEvent($subscription);
        $this->eventDispatcher->dispatch( $subscriptionEvent, SubscriptionEvents::DISABLE_SUBSCRIPTION);
    }

    /**
     * Get strategy from subscription.
     *
     * @param SubscriptionInterface $subscription
     *
     * @return SubscriptionStrategyInterface
     *
     * @throws StrategyNotFoundException
     */
    private function getStrategyFromSubscription(SubscriptionInterface $subscription)
    {
        $strategyName = $subscription->getStrategy();

        return $this->registry->get($strategyName);
    }

    /**
     * Check subscription integrity.
     *
     * @param SubscriptionInterface $subscription
     *
     * @throws SubscriptionIntegrityException
     */
    private function checkSubscriptionIntegrity(SubscriptionInterface $subscription)
    {
        if (null === $subscription->getProduct()) {
            throw new SubscriptionIntegrityException('Subscription must have a product defined.');
        }

        if (null === $subscription->getUser()) {
            throw new SubscriptionIntegrityException('Subscription must have a user defined.');
        }
    }

    /**
     * Check if subscription is auto-renewable.
     *
     * @param SubscriptionInterface $subscription
     *
     * @throws SubscriptionRenewalException
     */
    private function checkSubscriptionRenewable(SubscriptionInterface $subscription)
    {
        if (null === $subscription->getEndDate()) {
            throw new SubscriptionRenewalException('A permanent subscription can not be renewed.');
        }

        if (!$subscription->isAutoRenewal()) {
            throw new SubscriptionRenewalException('The current subscription is not auto-renewal.');
        }

        if (!$subscription->getProduct()->isAutoRenewal()) {
            throw new SubscriptionRenewalException(sprintf(
                'The product "%s" is not auto-renewal. Maybe is disabled?',
                $subscription->getProduct()->getName()
            ));
        }
    }

    /**
     * @param SubscriptionInterface $subscription
     *
     * @throws SubscriptionStatusException
     */
    private function checkSubscriptionNonActive(SubscriptionInterface $subscription)
    {
        if (!$subscription->isActive()) {
            return;
        }

        throw new SubscriptionStatusException('Subscription is active.');
    }

    /**
     * @param SubscriptionInterface $subscription
     *
     * @throws SubscriptionStatusException
     */
    private function checkSubscriptionActive(SubscriptionInterface $subscription)
    {
        if ($subscription->isActive()) {
            return;
        }

        throw new SubscriptionStatusException('Subscription is not active.');
    }
}
