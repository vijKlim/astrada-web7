<?php


namespace App\Factory;


use App\Entity\Booking;
use App\Entity\Listing;
use App\Entity\Post;
use App\Entity\Topic;
use Doctrine\ORM\EntityRepository;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;


class TopicFactory implements FactoryInterface
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var CustomerContextInterface
     */
    protected $customerContext;



    /**
     * @var FactoryInterface
     */
    protected $postFactory;

    /**
     * @param string $className
     */
    public function __construct(
        string $className,
        CustomerContextInterface $customerContext,
        FactoryInterface $postFactory
    ) {
        $this->className = $className;
        $this->customerContext = $customerContext;
        $this->postFactory = $postFactory;
    }

    /**
     * @return Topic
     */
    public function createNew()
    {
        /** @var Topic $topic */
        $topic = new $this->className();
        $topic->setAuthor($this->customerContext->getCustomer());

        /** @var Post $mainPost */
        $mainPost = $this->postFactory->createNew();
        $topic->setMainPost($mainPost);

        return $topic;
    }

    /**
     * @param Booking $booking
     *
     * @return Topic
     */
    public function createForBooking(Booking $booking)
    {
        /** @var Topic $topic */
        $topic = $this->createNew();
        $topic->setMainPost(null); // topic for booking has no main post

        $booking
            ->setTopic($topic);

        $topic->setTitle((string) $booking->getMessage());
        $topic->setAuthor($booking->getUser()->getCustomer());

        return $topic;
    }
}