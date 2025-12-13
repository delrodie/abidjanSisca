<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Instance;
use App\Entity\User;
use App\Entity\Utilisateur;
use App\Enum\InstanceType;
use App\Repository\InstanceRepository;
use App\Repository\OrganeRepository;
use App\Repository\UtilisateurRepository;
use App\Services\GenerateCompte;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/generate-compte')]
class GenerateCompteController extends AbstractController
{
    public function __construct(
        private readonly UtilisateurRepository $utilisateurRepository,
        private readonly InstanceRepository    $instanceRepository,
        private readonly GenerateCompte $generateCompte,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly EntityManagerInterface $entityManager,
        private readonly OrganeRepository $organeRepository
    )
    {
    }

    #[Route('/', name: 'admin_generate_compte')]
    public function index(): Response
    {
        return $this->render('admin/generate_compte.html.twig',[
            'instances' => $this->instanceRepository->findAllNotCompte(),
        ]);
    }

    #[Route('/{slug:instance}', name: 'admin_generate_compte_add', methods: ['GET'])]
    public function add(Instance $instance)
    {
        if (!$instance && $instance->getType() !== InstanceType::DISTRICT && $instance->getType()->value !== InstanceType::GROUPE){
            $this->addFlash('error', "Vous ne pouvez que générer les comptes des groupes ou districts!");
            return $this->redirect('/admin/utilisateur');
        }
        $username = $this->generateCompte->generate($instance->getNom());
        $plainPassword = $this->generateCompte->plainPassword();
        $role = $this->getRole($instance->getType());

        $user = new User();
        $user->setUsername($username);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $plainPassword));
        $user->setRoles($role);
        $this->entityManager->persist($user);

        $utilisateur = new Utilisateur();
        $utilisateur->setNom($instance->getType()->value);
        $utilisateur->setPrenom($instance->getNom());
        $utilisateur->setUsername($username);
        $utilisateur->setUserpass($plainPassword);
        $utilisateur->setActif(true);
        $utilisateur->setOrgane($this->getOrgane($instance->getType()));
        $utilisateur->setInstance($instance);
        $utilisateur->setUser($user);
        $utilisateur->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($utilisateur);
        $this->entityManager->flush();

        $this->addFlash('success', "Compte de {$instance->getNom()} généré avec succès!");
        return $this->redirect('/admin?routeName=admin_generate_compte');
    }

    private function getRole($value): array
    {
        return match ($value){
            InstanceType::GROUPE => ['ROLE_USER', 'ROLE_GROUPE'],
            InstanceType::DISTRICT => ['ROLE_USER', 'ROLE_DISTRICT'],
            default => ['ROLE_USER']
        };
    }
    private function getOrgane($value)
    {
        return match ($value){
            InstanceType::DISTRICT => $this->organeRepository->findOneBy(['nom' => 'District']),
            default => $this->organeRepository->findOneBy(['nom' => 'Groupe'])
        };
    }


}
