<?php


namespace App\Entity;

use App\Entity\Task\CollectionTrait as TaskCollectionTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A TaskCollection is the database representation of a Task\CollectionInterface.
 * It uses Doctrine's Inheritance Mapping to implement a OneToMany relationship with TaskCollectionItem.
 * There are two concrete implementations of TaskCollection: Delivery & TaskList.
 *
 * @see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/inheritance-mapping.html
 */
abstract class TaskCollection
{
    use TaskCollectionTrait;

    protected $id;

    /**
     * @Assert\Valid()
     * @Groups({"task_collection", "task"})
     */
    protected $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return ArrayCollection
     */
    public function getItems(): ArrayCollection
    {
        $iterator = $this->items->getIterator();

        $iterator->uasort(function ($a, $b) {
            if($a->getPosition() === $b->getPosition()) {
                return 0;
            }

            return $a->getPosition < $b->getPosition() ? -1 : 1;
        });

        return new ArrayCollection(iterator_to_array($iterator));
    }


    public function getTasks()
    {
        return $this->getItems()->map(function (TaskCollectionItem $item) {
            return $item->getTask();
        })->toArray();
    }
}