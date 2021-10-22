<?php


namespace App\Entity;


use App\Entity\Sylius\Customer;
use App\Sylius\Customer\CustomerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Resource\Model\ResourceInterface;
use Gedmo\Timestampable\Traits\Timestampable;
use Symfony\Component\Validator\Constraints as Assert;

class Topic implements ResourceInterface
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
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * @var CustomerInterface
     */
    protected $author;

    /**
     * @var Post
     * @Assert\Valid()
     */
    protected $mainPost;

    /**
     * @var ArrayCollection
     */
    protected $posts;

    /**
     * @var int
     */
    protected $postCount;

    /**
     * @var int
     */
    protected $viewCount = 0;

    /**
     * @var \DateTime
     */
    protected $lastPostCreatedAt;

    /**
     * @var Booking
     */
    protected $booking;

    /**
     * @var ArrayCollection|CustomerInterface[]
     */
    protected $followers;

    /**
     * Topic constructor.
     */
    public function __construct()
    {
        $this->code = uniqid('topic_');
        $this->posts = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->postCount = 0;
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
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return int
     */
    public function getPostCount(): int
    {
        return $this->postCount;
    }

    /**
     * @param int $postCount
     */
    public function setPostCount(int $postCount): void
    {
        $this->postCount = $postCount;
    }

    /**
     * @return int
     */
    public function getViewCount(): int
    {
        return $this->viewCount;
    }

    /**
     * @param int $viewCount
     */
    public function setViewCount(int $viewCount): void
    {
        $this->viewCount = $viewCount;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastPostCreatedAt(): ?\DateTime
    {
        return $this->lastPostCreatedAt;
    }

    /**
     * @param \DateTime|null $lastPostCreatedAt
     */
    public function setLastPostCreatedAt(?\DateTime $lastPostCreatedAt): void
    {
        $this->lastPostCreatedAt = $lastPostCreatedAt;
    }

    /**
     * @return Post|null
     */
    public function getFirstPost(): ?Post
    {
        $firstPost = $this->posts->first();

        return $firstPost ?: null;
    }

    /**
     * @return Post|null
     */
    public function getLastPost(): ?Post
    {
        $lastPost = $this->posts->last();

        return $lastPost ?: null;
    }

    /**
     * @return CustomerInterface|Customer|null
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

    /**
     * @return Post|null
     */
    public function getMainPost(): ?Post
    {
        return $this->mainPost;
    }

    /**
     * @param Post|null $mainPost
     */
    public function setMainPost(?Post $mainPost): void
    {
        $this->mainPost = $mainPost;
    }

    /**
     * @return Post[]|Collection
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    /**
     * @param Post $post
     */
    public function addPost(Post $post): void
    {
        if (!$this->hasPost($post)) {
            $this->posts->add($post);
            $post->setTopic($this);
        }
    }

    /**
     * @param Post $post
     */
    public function removePost(Post $post): void
    {
        $this->posts->removeElement($post);
        $post->setTopic(null);
    }

    /**
     * @param Post $post
     *
     * @return bool
     */
    public function hasPost(Post $post): bool
    {
        return $this->posts->contains($post);
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
     * @return Collection|CustomerInterface[]
     */
    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    /**
     * @param CustomerInterface $follower
     */
    public function addFollower(CustomerInterface $follower): void
    {
        if (!$this->hasFollower($follower)) {
            $this->followers->add($follower);
        }
    }

    /**
     * @param CustomerInterface $follower
     */
    public function removeFollower(CustomerInterface $follower): void
    {
        $this->followers->removeElement($follower);
    }

    /**
     * @param CustomerInterface $follower
     *
     * @return bool
     */
    public function hasFollower(CustomerInterface $follower): bool
    {
        return $this->followers->contains($follower);
    }

    /**
     * @param bool $nullForFirstPage
     *
     * @return int|null
     */
    public function getLastPageNumber($nullForFirstPage = true): ?int
    {
        $pageNumber = ceil($this->postCount / 10);

        if ($nullForFirstPage) {
            return $pageNumber > 1 ? $pageNumber : null;
        }

        return $pageNumber;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getTitle();
    }
}
