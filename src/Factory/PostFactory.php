<?php


namespace App\Factory;



use App\Entity\Booking;
use App\Entity\Post;
use App\Entity\Topic;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

/**
 * @author Loïc Frémont <loic@mobizel.com>
 */
class PostFactory implements FactoryInterface
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
     * @param string $className
     * @param CustomerContextInterface $customerContext
     */
    public function __construct($className, CustomerContextInterface $customerContext)
    {
        $this->className = $className;
        $this->customerContext = $customerContext;
    }

    /**
     * @return Post
     */
    public function createNew()
    {
        /** @var Post $post */
        $post = new $this->className();
        $post->setAuthor($this->customerContext->getCustomer());

        return $post;
    }

    /**
     * @param Topic $topic
     *
     * @return Post
     */
    public function createForTopic($topic)
    {
        /** @var Post $post */
        $post = $this->createNew();

        $post
            ->setTopic($topic);

        return $post;
    }

    /**
     * @param Booking $booking
     *
     * @return Post
     */
    public function createForBooking(Booking $booking)
    {
        /** @var Post $post */
        $post = $this->createNew();

        $post
            ->setBooking($booking);

        return $post;
    }


}
