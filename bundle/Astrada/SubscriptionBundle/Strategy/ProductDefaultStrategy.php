<?php

namespace Astrada\SubscriptionBundle\Strategy;



use Astrada\SubscriptionBundle\Model\SubscriptionProductInterface;
use Astrada\SubscriptionBundle\Exception\ProductDefaultNotFoundException;
use Astrada\SubscriptionBundle\Exception\ProductExpiredException;
use Astrada\SubscriptionBundle\Exception\ProductIntegrityException;
use Astrada\SubscriptionBundle\Exception\ProductQuoteExceededException;

class ProductDefaultStrategy extends AbstractProductStrategy
{
    /**
     * {@inheritdoc}
     */
    public function getFinalProduct(SubscriptionProductInterface $product)
    {
        try {

            $this->checkProductIntegrity($product);
            $this->checkExpiration($product);
            $this->checkQuote($product);

            return $product;

        } catch (ProductIntegrityException $exception) {

            $this->getLogger()->error('Product integrity: {message}', [
                'message' => $exception->getMessage()
            ]);

        } catch (ProductExpiredException $exception) {

            $this->getLogger()->error('Product is expired: {message}', [
                'message' => $exception->getMessage()
            ]);

        } catch (ProductQuoteExceededException $exception) {

            $this->getLogger()->error('Product quota is exceeded: {message}', [
                'message' => $exception->getMessage()
            ]);

        }

        return $this->getDefaultProduct();
    }

    /**
     * Get default product in case of that current product is not valid.
     *
     * @return SubscriptionProductInterface
     *
     * @throws ProductDefaultNotFoundException
     */
    private function getDefaultProduct()
    {
        $defaultProduct = $this->getProductRepository()->findDefault();

        if (null !== $defaultProduct) {
            return $defaultProduct;
        }

        throw new ProductDefaultNotFoundException('Default product was not found into the product repository');
    }
}
