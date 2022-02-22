<?php


namespace App\Entity\Listener;


use App\Entity\Task;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class TaskListener
{
    public function prePersist(Task $task, LifecycleEventArgs $args)
    {
        if (null === $task->getDoneAfter()) {
            $doneAfter = clone $task->getDoneBefore();
            $doneAfter->modify('-15 minutes');
            $task->setDoneAfter($doneAfter);
        }
    }
}