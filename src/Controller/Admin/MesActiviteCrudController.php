<?php

namespace App\Controller\Admin;

use App\Entity\Activite;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class MesActiviteCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly UtilisateurRepository $utilisateurRepository
    )
    {
    }

    public static function getEntityFqcn(): string
    {
        return Activite::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),

            FormField::addColumn('col-md-6 mt-3 mt-md-5'),
            TextField::new('reference')->hideOnForm(),
            TextField::new('denomination')
                ->setLabel('Dénomination de l\'activité')
                ->setRequired(true),
            TextEditorField::new('objectif', "Objectifs de l'activité")
                ->setRequired(true)
                ->hideOnIndex(),

//            FormField::addColumn('col-md-3 mt-3 mt-md-5'),

            FormField::addColumn('col-md-6 mt-3 mt-md-5'),
            TextField::new('lieu', "Lieu où se tiendra l'activité"),
            TextEditorField::new('contenu', "Contenu de l'activité")
                ->setRequired(true)
                ->hideOnIndex(),

            FormField::addColumn('col-12'),
            FormField::addFieldset('Période prévisionnelle'),
            DateField::new('dateDebut', "Date début")->setColumns(6),
            DateField::new('dateFin', "Date fin")->setColumns(6),

            FormField::addFieldset(),
            TextField::new('responsable', "Responsable ou organisateur de l'activité")
                ->setColumns(4)
                ->setRequired(true)
                ->hideOnIndex(),
            ArrayField::new('cible')
                ->setLabel('Qualités des participants / cibles')
                ->setColumns(4)
                ->setRequired(true),
            ArrayField::new('partiePrenante', "Partie prénantes impactantes pour la réalisation de l'activité")
                ->setColumns(4)
                ->hideOnIndex(),

            FormField::addFieldset('Bloc administrateur')
                ->setPermission('ROLE_AT'),
            AssociationField::new('instance', "Instance")
                ->setColumns(4)
                ->setPermission('ROLE_AT'),

            ChoiceField::new('statut', 'Statut')
                ->setChoices([
                    'Brouillon' => 'brouillon',
                    'Attente district' => 'attente_district',
                    'Approuvée district' => 'approuve_district',
                    'Attente Région' => 'attente_region',
                    'Validée' => 'validee',
                    'Rejetée' => 'rejetee',
                ])
                ->renderAsBadges([
                    'brouillon' => 'secondary',
                    'attente_district' => 'warning',
                    'approuve_district' => 'info',
                    'attente_region' => "warning",
                    'validee' => 'success',
                    'rejetee' => 'danger'
                ])
                ->onlyOnIndex()
        ];
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $user = $this->getUser();

        // Alias de l'entité principale
        $rootAlias = $queryBuilder->getRootAliases()[0]; ///dd($rootAlias.);

        // Récupérer le compte associé à l'utilisateur connecté
        // Si l'utilisateur n'est pas associé a un compte ou instance, alors renvoyer une liste vide
        $utilisateur = $this->utilisateurRepository->findOneBy(['user' => $user]);
        if (!$utilisateur || !$utilisateur->getInstance()){
            $queryBuilder->andWhere('entity.id = :no_result')->setParameter('no_result', -1);
            return $queryBuilder;
        }

        $instanceUtilisateur = $utilisateur->getInstance();

        // Récupération du type de l'instance
        $instanceType = $instanceUtilisateur->getType()->name;

        // Si l'instance de l'utilisateur est de type Groupe
        if ($instanceType === 'GROUPE' || $instanceType === 'DISTRICT'){
            $queryBuilder
                ->andWhere("{$rootAlias}.instance IN (:instanceId)")
                ->setParameter('instanceId', $instanceUtilisateur->getId())
            ;
        }

        if ($instanceType === 'REGION'){
            $auteurIds = [$utilisateur->getId()];
            $organe = $utilisateur->getOrgane();
            $utilisateurs = $this->utilisateurRepository->findBy(['organe' => $organe]);

            foreach ($utilisateurs as $user){
                $auteurIds[] = $user->getId();
            }

            $queryBuilder
                ->andWhere("{$rootAlias}.auteur IN (:auteurIds)")
                ->setParameter('auteurIds', $auteurIds)
                ;
        }

        return $queryBuilder;

        // Si role est groupe ou district alors même instance

        // Sinon si region alors verifier l'organe de l'utilisateur et recuperer la liste des activités de l'organe.
    }

}
