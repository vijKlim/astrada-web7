<?php


namespace App\Entity;

use App\Entity\Task\CollectionInterface as TaskCollectionInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Symfony\Component\Serializer\Annotation\Groups;

class Delivery extends TaskCollection implements TaskCollectionInterface
{

    /**
     * @Groups({"delivery"})
     */
    protected $id;

    private $order;


    public function __construct()
    {
        parent::__construct();

    }

    public function getOrder()
    {
        return $this->order;
    }

    public function setOrder(OrderInterface $order)
    {
        $this->order = $order;

        return $this;
    }

    public static function create()
    {
        return new self();
    }


}