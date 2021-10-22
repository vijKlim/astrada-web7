<?php

namespace Astrada\SubscriptionBundle\Strategy;

use Astrada\SubscriptionBundle\Model\SubscriptionProductInterface;
use Astrada\SubscriptionBundle\Repository\ProductRepositoryInterface;

use Astrada\SubscriptionBundle\Exception\ProductExpiredException;
use Astrada\SubscriptionBundle\Exception\ProductIntegrityException;
use Astrada\SubscriptionBundle\Exception\ProductQuoteExceededException;
use Astrada\SubscriptionBundle\Repository\SubscriptionRepositoryInterface;
use Monolog\Logger;


abstract class AbstractProductStrategy implements ProductStrategyInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SubscriptionRepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param ProductRepositoryInterface      $productRepository
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     * @param Logger                          $logger
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        SubscriptionRepositoryInterface $subscriptionRepository,
        Logger $logger
    )
    {
        $this->productRepository      = $productRepository;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->logger                 = $logger;
    }

    /**
     * @return ProductRepositoryInterface
     */
    protected function getProductRepository()
    {
        return $this->productRepository;
    }

    /**
     * @return SubscriptionRepositoryInterface
     */
    protected function getSubscriptionRepository()
    {
        return $this->subscriptionRepository;
    }

    /**
     * @return Logger
     */
    protected function getLogger()
    {
        return $this->logger;
    }

    /**
     * Check the product model integrity.
     *
     * @param SubscriptionProductInterface $product
     *
     * @throws ProductIntegrityException
     */
    final public function checkProductIntegrity(SubscriptionProductInterface $product)
    {
        if ($product->isDefault() && null !== $product->getQuota()) {

            throw new ProductIntegrityException(sprintf(
                'The product "%s" is a default product with a quota (%s). Default products can not have a quote value.',
                $product->getName(),
                $product->getQuota()
            ));

        }

        if ($product->isDefault() && null !== $product->getExpirationDate()) {

            throw new ProductIntegrityException(sprintf(
                'The product "%s" is a default product with expiration date (%s). Default products can not have a expiration date.',
                $product->getName(),
                $product->getExpirationDate()->format('Y-m-d H:i:s')
            ));

        }
    }

    /**
     * Check product expiration.
     *
     * @param SubscriptionProductInterface $product
     *
     * @throws ProductExpiredException
     */
    public function checkExpiration(SubscriptionProductInterface $product)
    {
        $expirationDate = $product->getExpirationDate();

        if (null === $expirationDate || new \DateTime() <= $expirationDate) {
            return;
        }

        throw new ProductExpiredException(sprintf(
            'The product "%s" has been expired at %s.',
            $product->getName(),
            $expirationDate->format('Y-m-d H:i:s')
        ));
    }

    /**
     * Check product quote.
     *
     * @param SubscriptionProductInterface $product
     *
     * @throws ProductQuoteExceededException
     */
    public function checkQuote(SubscriptionProductInterface $product)
    {
        // Unlimited quote
        if (null === $product->getQuota()) {
            return;
        }

        // Calculate the current quote
        $currentQuote = $this->subscriptionRepository->getNumberOfSubscriptionsByProducts($product);

        if ($currentQuote < $product->getQuota()) {
            return;
        }

        throw new ProductQuoteExceededException(sprintf(
            'The product "%s" quota is %s. This is exceeded. Increase the quota.',
            $product->getName(),
            $product->getQuota()
        ));
    }
}
