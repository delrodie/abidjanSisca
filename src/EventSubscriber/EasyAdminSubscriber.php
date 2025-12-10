<?php

namespace App\EventSubscriber;

use App\Entity\Organe;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\String\Slugger\AsciiSlugger;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    public function __construct()
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => ['setOrganeSlug'],
        ];
    }

    public function setOrganeSlug(BeforeEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if(!($entity instanceof Organe)) {
            return;
        }

        $slug = strtolower(new AsciiSlugger()->slug($entity->getNom()));
        $entity->setSlug($slug);
    }
}
