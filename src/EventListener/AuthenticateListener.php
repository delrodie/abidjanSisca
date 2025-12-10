<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;

final readonly class AuthenticateListener
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[AsEventListener(event: 'security.authentication.success')]
    public function onSecurityAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();
        if ($user instanceof User){
            $user->setConnexion((int) $user->getConnexion() + 1);
            $user->setLastConnectedAt(new \DateTimeImmutable());

            $this->entityManager->flush();
        }
    }
}
