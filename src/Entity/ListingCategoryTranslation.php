<?php


namespace App\Entity;



use Sylius\Component\Resource\Model\AbstractTranslation;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ListingCategoryTranslation  extends AbstractTranslation  implements ResourceInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @Assert\NotBlank(message="assert.not_blank")
     * @Assert\NotNull(message="assert.not_blank")
     * @Assert\Length(
     *      min = "3",
     *      max = "150",
     *      minMessage = "assert.min_length {{ limit }}",
     *      maxMessage = "assert.max_length {{ limit }}"
     * )
     *
     * @var string
     */
    protected $name;

    /** @var string|null */
    protected $slug;

    /**
     * @Assert\NotBlank(message="assert.not_blank")
     * @Assert\NotNull(message="assert.not_blank")
     *
     * @var string
     */
    protected $description;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->name;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

}