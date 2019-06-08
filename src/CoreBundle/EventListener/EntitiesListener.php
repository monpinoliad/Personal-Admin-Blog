<?php

namespace CoreBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;


/**
 * This class is for the Entities listener and permit to add changes by the doctrine lifecycle workflow.
 *
 * @package CoreBundle\EventListener
 *
 */
class EntitiesListener
{
    /**
     * Insert current date to createdAt for worker.
     *
     * @param LifecycleEventArgs $args
     *
     * @throws \Exception
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entity->setCreatedAt(new \DateTime());
    }

    /**
     * This function will put the current Datetime every time you update an entity.
     *
     * @param LifecycleEventArgs $args
     * @throws \Exception
     *
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entity->setUpdatedAt(new \DateTime());
    }
}