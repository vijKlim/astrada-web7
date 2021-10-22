<?php


namespace App\Controller;


use App\Entity\Listing;
use App\Entity\ListingSubscription;
use App\Entity\User;
use Astrada\SubscriptionBundle\SubscriptionManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class SubscriptionController extends AbstractController
{
    private $subscriptionManager;

    public function __construct(SubscriptionManager $subscriptionManager)
    {
        $this->subscriptionManager = $subscriptionManager;
    }

    public function buyAction(Request $request, $product)
    {
        $product = $this->getProductRepository()->find($product);
        $user = $this->getUser();

        try {

            $subscription = $this->subscriptionManager->create($product, $user);


            // You can do this step in other service (or moment) if you want.
            // You must activate the subscription explicitly!
            $this->subscriptionManager->activate($subscription);

            $this->save($subscription);

        } catch(\Exception $exception) {
            $request->getSession()
                ->getFlashBag()
                ->add('error', $exception->getMessage());
        }

        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
    }

    public function disableAction($id)
    {
        $subscription = $this->getSubscription($id);

        try {

            $this->get('astrada.subscription.manager')->disable($subscription);
            $this->save($subscription);

        } catch(\Exception $exception) {

            return $this->renderIndex($exception);
        }

        return $this->renderIndex();
    }

    public function expireAction($id)
    {
        $subscription = $this->getSubscription($id);

        try {

            $this->get('astrada.subscription.manager')->expire($subscription);
            $this->save($subscription);

        } catch(\Exception $exception) {

            return $this->renderIndex($exception);
        }

        return $this->renderIndex();
    }

    public function renewAction($id)
    {
        $subscription = $this->getSubscription($id);

        try {

            $newSubscription = $this->get('astrada.subscription.manager')->renew($subscription);
            $this->save($subscription);
            $this->save($newSubscription);

        } catch(\Exception $exception) {

            return $this->renderIndex($exception);
        }

        return $this->renderIndex();
    }


    private function getSubscription($id)
    {
        return $this->getSubscriptionRepository()->find($id);
    }

    private function save($object)
    {
        $this->getDoctrine()->getManager()->persist($object);
        $this->getDoctrine()->getManager()->flush();
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    private function getSubscriptionRepository()
    {
        return $this->getDoctrine()->getRepository(ListingSubscription::class);
    }

    private function getProductRepository()
    {
        return $this->getDoctrine()->getRepository(Listing::class);
    }

    private function getUserRepository()
    {
        return $this->getDoctrine()->getRepository(User::class);
    }
}