<?php


namespace App\Entity\Listener;


use App\Entity\Delivery;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class DeliveryListener
{
    public function prePersist(Delivery $delivery, LifecycleEventArgs $args)
    {
        $comments = '';

        if ($delivery->hasPackages()) {
            foreach ($delivery->getPackages() as $package) {
                $comments .= $package->getQuantity() .' × ' . $package->getPackage()->getName();
                $comments .= "\n";
            }
        }

        $grams = $delivery->getWeight();
        if (null !== $grams) {
            $weight = number_format($grams / 1000, 2) . ' kg';
            $comments .= $weight;
        }

        $prevComments = $delivery->getPickup()->getComments();

        $delivery->getPickup()->setComments(
            $prevComments ? ($prevComments . "\n\n" . $comments) : $comments
        );

        $store = $delivery->getStore();

        if (null === $store) {
            return;
        }

        $tags = $store->getTags();

        foreach ($delivery->getTasks() as $task) {
            $task->addTags($tags);
        }
    }
}