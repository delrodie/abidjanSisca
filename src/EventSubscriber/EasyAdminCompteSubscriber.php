<?php

namespace App\EventSubscriber;

use App\Entity\Organe;
use App\Entity\User;
use App\Entity\Utilisateur;
use App\Repository\UserRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\DBAL\Exception\ConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\ValidatorException;

class EasyAdminCompteSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UtilisateurRepository       $utilisateurRepository,
        private readonly UserRepository              $userRepository,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => 'userCreate',
            BeforeEntityUpdatedEvent::class => 'userUpdate',
        ];
    }

    public function userCreate(BeforeEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();
        if (!$entity instanceof Utilisateur){
            return;
        }

        // Gestion des User
        $user = new User();
        $user->setUsername($entity->getUsername());
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $entity->getUserpass())
        );
        $user->setRoles($this->determineRole($entity->getOrgane()->getRole()));

        $this->entityManager->persist($user);

        // Mise a jour Utilisateur
        $entity->setCreatedAt(new \DateTimeImmutable());
        $entity->setUser($user);
        $this->entityManager->persist($entity);

        $this->entityManager->flush();

    }

    public function userUpdate(BeforeEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();
        if (!$entity instanceof Utilisateur){
            return;
        }

        if (!$entity->getUser() instanceof User){
            return;
        }

        $user = $entity->getUser();
        $user->setUsername($entity->getUsername());
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $entity->getUserpass())
        );
        $user->setRoles($this->determineRole($entity->getOrgane()->getRole()));

        // Mise a jour Utilisateur
        $entity->setUser($user);

        $this->entityManager->flush();
    }

    /**
     * Determination des roles
     * @param string $organe
     * @return string[]
     */
    private function determineRole(string $organe): array
    {
        return match ($organe) {
            'ROLE_ADMIN' => ['ROLE_USER', 'ROLE_ADMIN'],
            'ROLE_PSEUDO_ADMIN' => ['ROLE_USER', 'ROLE_PSEUDO_ADMIN'],
            'ROLE_AT' => ['ROLE_USER', 'ROLE_AT'],
            'ROLE_DISTRICT' => ['ROLE_USER', 'ROLE_DISTRICT'],
            'ROLE_GROUPE' => ['ROLE_USER', 'ROLE_GROUPE'],
            default => ['ROLE_USER', 'ROLE_REGION']
        };

    }
}
