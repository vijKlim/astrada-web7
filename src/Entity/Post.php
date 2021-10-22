<?php


namespace App\Entity;

use App\Sylius\Customer\CustomerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Timestampable\Traits\Timestampable;
use Sylius\Component\Resource\Model\ResourceInterface;

class Post implements ResourceInterface
{
    use Timestampable;

    /**
     *
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $body;

    /**
     * @var Topic
     */
    protected $parent;

    /**
     * @var Topic
     */
    protected $topic;



    /**
     * @var Booking
     */
    protected $booking;

    /**
     * @var CustomerInterface
     */
    protected $author;

    /**
     * @var Post
     */
    protected $replyTo;

    /**
     * @var ArrayCollection
     */
    protected $replies;

    /**
     * Post constructor.
     */
    public function __construct()
    {
        $this->replies = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string|null $code
     */
    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return string|null
     */
    public function getBody(): ? string
    {
        return $this->body;
    }

    /**
     * @param string|null $body
     */
    public function setBody(?string $body): void
    {
        $this->body = $body;
    }

    /**
     * @return Topic|null
     */
    public function getParent(): ?Topic
    {
        return $this->parent;
    }

    /**
     * @param Topic|null $parent
     */
    public function setParent(?Topic $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return Topic|null
     */
    public function getTopic(): ?Topic
    {
        return $this->topic;
    }

    /**
     * @param Topic|null $topic
     */
    public function setTopic(?Topic $topic): void
    {
        $this->topic = $topic;

        if (null !== $topic && !$topic->hasPost($this)) {
            $topic->addPost($this);
        }
    }


    /**
     * @return Booking|null
     */
    public function getBooking(): ?Booking
    {
        return $this->booking;
    }

    /**
     * @param Booking|null $booking
     */
    public function setBooking(?Booking $booking): void
    {
        $this->booking = $booking;
    }

    /**
     * @return CustomerInterface|null
     */
    public function getAuthor(): ?CustomerInterface
    {
        return $this->author;
    }

    /**
     * @param CustomerInterface|null $author
     */
    public function setAuthor(?CustomerInterface $author): void
    {
        $this->author = $author;
    }
}