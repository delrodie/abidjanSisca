<?php

namespace App\EventSubscriber;

use App\Entity\Activite;
use App\Entity\Organe;
use App\Repository\UtilisateurRepository;
use App\Services\GestionActivite;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\String\Slugger\AsciiSlugger;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly GestionActivite $gestionActivite,
        private readonly Security        $security, private readonly UtilisateurRepository $utilisateurRepository
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => ['setOrganeSlug'],
            BeforeEntityPersistedEvent::class => ['setReferenceActivite'],
        ];
    }

    public function setOrganeSlug(BeforeEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        // Gestion des Organes
        if(!($entity instanceof Organe)) {
            return;
        }

        $slug = strtolower(new AsciiSlugger()->slug($entity->getNom()));
        $entity->setSlug($slug);
    }

    public function setReferenceActivite(BeforeEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();
        if(!($entity instanceof Activite)) {
            return;
        }
        $instance = $entity->getInstance();
        $compte = $this->utilisateurRepository->findOneBy(['user' => $this->security->getUser()]);

        if (!$instance){
            $instance = $compte?->getInstance();
        }

        if (!$instance) {
            return;
        }

        if ($compte){
            $entity->setAuteur($compte);
        }

        $entity->setInstance($instance);
        $entity->setReference($this->gestionActivite->generateReference($instance->getType()));
    }
}
