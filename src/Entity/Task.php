<?php


namespace App\Entity;

use App\DataType\TsRange;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class Task
{
    const TYPE_DROPOFF = 'DROPOFF';
    const TYPE_PICKUP = 'PICKUP';

    const STATUS_TODO = 'TODO';
    const STATUS_DOING = 'DOING';
    const STATUS_FAILED = 'FAILED';
    const STATUS_DONE = 'DONE';
    const STATUS_CANCELLED = 'CANCELLED';

    /**
     * The radius (in meters) that is used for geofences.
     * @var int
     */
    const GEOFENCING_RADIUS = 300;

    /**
     * @Groups({"task", "delivery"})
     */
    private $id;

    /**
     * @Assert\Choice({"PICKUP", "DROPOFF"})
     * @Groups({"task", "task_create", "task_edit", "delivery_create"})
     */
    private $type = self::TYPE_DROPOFF;

    /**
     * @Groups({"task", "delivery"})
     */
    private $status = self::STATUS_TODO;

    private $delivery;

    /**
     * @Assert\NotNull()
     * @Assert\Valid()
     * @Groups({"task", "task_create", "task_edit", "address", "address_create", "delivery_create"})
     */
    private $address;

    private $doneAfter;

    /**
     * @Assert\NotBlank()
     * @Assert\Expression(
     *     "this.getDoneAfter() == null or this.getDoneAfter() < this.getDoneBefore()",
     *     message="task.before.mustBeGreaterThanAfter"
     * )
     */
    private $doneBefore;

    /**
     * @Groups({"task", "task_create", "task_edit", "delivery", "delivery_create"})
     */
    private $comments;



    private $createdAt;

    /**
     * @Groups({"task"})
     */
    private $updatedAt;

    private $previous;

    private $next;


    /**
     * @var array
     * @Groups({"task"})
     */
    private $metadata = [];


    public function __construct()
    {

    }

    public function getId()
    {
        return $this->id;
    }

    public function getDelivery()
    {
        return $this->delivery;
    }

    public function setDelivery($delivery)
    {
        $this->delivery = $delivery;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function isPickup()
    {
        return $this->type === self::TYPE_PICKUP;
    }

    public function isDropoff()
    {
        return $this->type === self::TYPE_DROPOFF;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function isDone()
    {
        return $this->status === self::STATUS_DONE;
    }

    public function isFailed()
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isCompleted()
    {
        return $this->isDone() || $this->isFailed();
    }

    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @SerializedName("after")
     * @Groups({"task", "task_edit", "delivery"})
     */
    public function getAfter()
    {
        return $this->doneAfter;
    }

    /**
     * @SerializedName("after")
     * @Groups({"task", "task_create", "task_edit", "delivery", "delivery_create"})
     */
    public function setAfter(?\DateTime $doneAfter)
    {
        $this->doneAfter = $doneAfter;

        return $this;
    }

    /**
     * @SerializedName("before")
     * @Groups({"task", "task_create", "task_edit", "delivery", "delivery_create"})
     */
    public function getBefore()
    {
        return $this->doneBefore;
    }

    /**
     * @SerializedName("before")
     * @Groups({"task", "task_edit", "delivery"})
     */
    public function setBefore(?\DateTime $doneBefore)
    {
        $this->doneBefore = $doneBefore;

        return $this;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }


    public function getPrevious()
    {
        return $this->previous;
    }

    public function setPrevious(Task $previous = null)
    {
        $this->previous = $previous;

        return $this;
    }

    public function hasPrevious()
    {
        return $this->previous !== null;
    }

    public function getNext()
    {
        return $this->next;
    }

    public function setNext(Task $next = null)
    {
        $this->next = $next;

        return $this;
    }

    public function hasNext()
    {
        return $this->next !== null;
    }


    public function duplicate()
    {
        $task = new self();

        $task->setType($this->getType());
        $task->setComments($this->getComments());
        $task->setAddress($this->getAddress());
        $task->setDoneAfter($this->getDoneAfter());
        $task->setDoneBefore($this->getDoneBefore());

        return $task;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }


    public function getTimeRange(): TsRange
    {
        $range = new TsRange();

        $range->setLower($this->getAfter());
        $range->setUpper($this->getBefore());

        return $range;
    }

    /* Legacy */

    public function getDoneAfter()
    {
        return $this->getAfter();
    }

    public function setDoneAfter(?\DateTime $after)
    {
        return $this->setAfter($after);
    }

    public function getDoneBefore()
    {
        return $this->getBefore();
    }

    public function setDoneBefore(?\DateTime $before)
    {
        return $this->setBefore($before);
    }



    public function setMetadata($key)
    {
        if (func_num_args() === 1 && is_array(func_get_arg(0))) {
            $this->metadata = func_get_arg(0);
        } elseif (func_num_args() === 2) {
            $this->metadata[func_get_arg(0)] = func_get_arg(1);
        }
    }

    public function getMetadata()
    {
        return $this->metadata;
    }

}