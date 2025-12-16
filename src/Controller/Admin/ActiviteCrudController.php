<?php

namespace App\Controller\Admin;

use AllowDynamicProperties;
use App\Entity\Activite;
use App\Enum\InstanceType;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Workflow\WorkflowInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;

#[AllowDynamicProperties]
class ActiviteCrudController extends AbstractCrudController
{
    public function __construct(
//        private readonly Activite $activite,
        #[AutowireLocator( 'workflow', 'name')]
        private ServiceLocator $workflows,
        private Security       $security,
        private readonly EntityManagerInterface $entityManager,
        private readonly UtilisateurRepository $utilisateurRepository
    )
    {
        $this->activiteWorkflow = $this->workflows->get('activite_workflow');
    }

    public static function getEntityFqcn(): string
    {
        return Activite::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des activités')
            ->setPageTitle('new', "Enregistrement d'une nouvelle activité")
            ->setPageTitle('edit', fn(Activite $activite) => sprintf('Modification de <b>%s</b>', $activite->getDenomination()))
            ->overrideTemplate('crud/detail', 'admin/activite_detail.html.twig')
//            ->overrideTemplate('crud/index', 'admin/activite_index.html.twig')
            ->setAutofocusSearch(true)
            ->setDefaultSort([
                'dateDebut' => 'ASC',
                'dateFin' => 'ASC',
            ])
            ;
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


    public function configureActions(Actions $actions): Actions
    {
        $activiteWorkflow = $this->workflows->get('activite_workflow');
        $soumettre = Action::new('soumettre', "Soumettre")
            ->linkToCrudAction('soumettreActivite')
            ->displayIf(fn(Activite $a) =>
                $activiteWorkflow->can($a, 'soumettre_groupe')
                || $activiteWorkflow->can($a, 'soumettre_district')
                || $activiteWorkflow->can($a, 'soumettre_direct_region')
            );

        $approuver = Action::new('approuver', 'Approuver')
            ->linkToCrudAction('ApprouverActivite')
            ->displayIf(fn(Activite $a) =>
                $activiteWorkflow->can($a, 'approuver_district')
            );

        $valider = Action::new('valider', "Valider")
            ->linkToCrudAction('validerActivite')
            ->displayIf(fn(Activite $a) =>
                $activiteWorkflow->can($a, 'valider_district')
                || $activiteWorkflow->can($a, 'valider_region')
            );

        $rejeter = Action::new('rejeter', 'Rejeter')
            ->linkToCrudAction('rejeterActivite')
            ->displayIf(fn(Activite $a) =>
            $activiteWorkflow->can($a, 'rejeter')
            );

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $soumettre)
            ->add(Crud::PAGE_INDEX, $approuver)
            ->add(Crud::PAGE_INDEX, $valider)
            ->add(Crud::PAGE_INDEX, $rejeter)
            ;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $user = $this->getUser();

        // Alias de l'entité principale
        $rootAlias = $queryBuilder->getRootAliases()[0];

        if (null === $user || $this->security->isGranted('ROLE_AT')){
            return $queryBuilder
                ;
        }

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
        if ($instanceType === 'GROUPE'){ //dd($instanceUtilisateur->getId());
            $queryBuilder
                ->andWhere("{$rootAlias}.instance IN (:instanceId)")
                ->setParameter('instanceId', $instanceUtilisateur->getId())
            ;
        }

        // Si l'instance de l'utilisateur est de type District
        if ($instanceType === 'DISTRICT'){
            $instanceIds = [$instanceUtilisateur->getId()];
            foreach ($instanceUtilisateur->getInstanceEnfants() as $enfant){
                $instanceIds[] = $enfant->getId();
            }
            $queryBuilder
                ->andWhere("{$rootAlias}.instance IN (:instanceIds)")
                ->setParameter('instanceIds', $instanceIds)
            ;
        }

        // Si l'instance de l'utilisateur est de type Region
        if ($instanceType === 'REGION'){
            $allInstanceIds = [$instanceUtilisateur->getId()];

            foreach ($instanceUtilisateur->getInstanceEnfants() as $district) {
                $allInstanceIds[] = $district->getId();
                foreach ($district->getInstanceEnfants() as $groupe) {
                    $allInstanceIds[] = $groupe->getId();
                }
            }
            $queryBuilder
                ->andWhere("{$rootAlias}.instance IN (:allInstanceIds)")
                ->setParameter('allInstanceIds', $allInstanceIds)
            ;
        }
        return $queryBuilder;
    }

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        if ($responseParameters->get('pageName') === Crud::PAGE_INDEX) {
            $entities = $responseParameters->get('entities');

            // Créer un mapping de couleurs pour chaque date unique
            $dateColors = [];
            $colors = ['#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6', '#1abc9c', '#e67e22', '#34495e'];
            $colorIndex = 0;

            foreach ($entities as $entity) {
                $dateDebut = $entity->getInstance()->getDateDebut();
                if ($dateDebut && !isset($dateColors[$dateDebut->format('Y-m-d')])) {
                    $dateColors[$dateDebut->format('Y-m-d')] = $colors[$colorIndex % count($colors)];
                    $colorIndex++;
                }
            }

            $responseParameters->set('dateColors', $dateColors);
        }

        return $responseParameters;
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addCssFile('styles/admin.css')
            ->addJsFile('js/admin.js')
            ;
    }

    #[AdminRoute(path: '/soumission', name:'admin_activite_soumission')]
    public function soumettreActivite(AdminContext $context)
    {
        $activite = $context->getEntity()->getInstance();
        $activiteWorkflow = $this->workflows->get('activite_workflow');


        // Gestion des soumissions si c'est un departement
        if (
            $this->security->isGranted('ROLE_REGION') ||
            $this->security->isGranted('ROLE_AT') ||
            $this->security->isGranted('ROLE_PSEUDO_ADMIN') ||
            $this->security->isGranted('ROLE_ADMIN')
        )
        {
            if ($activiteWorkflow->can($activite, 'soumettre_direct_region')) {
                $activiteWorkflow->apply($activite, 'soumettre_direct_region');
                $this->addFlash("success", "Activité soumise à la région avec succès!");
            }else{
                $this->addFlash('danger', "Impossible de soumettre : transition 'soumettre_direct_region' non disponible.");
            }
        }

        // Gestion des soumissions si c'est un district
        elseif ($this->security->isGranted('ROLE_DISTRICT'))
        {
            if ($activiteWorkflow->can($activite, 'soumettre_district')) {
                $activiteWorkflow->apply($activite, 'soumettre_district');
                $this->addFlash('success', "Activité soumise à la région avec succès");
            }else{
                $this->addFlash('danger', "Impossible de soumettre : transition 'soumettre_region' non disponible");
            }
        }

        // Gestion de soumission si c'est un groupe
        elseif ($this->security->isGranted('ROLE_GROUPE')){
            if ($activiteWorkflow->can($activite, 'soumettre_groupe')) {
                $activiteWorkflow->apply($activite, 'soumettre_groupe');
                $this->addFlash('success', "Activité soumise au District avec succès!");
            }else{
                $this->addFlash('danger', "Impossible de soumettre : transition 'soumettre_district' non disponible.");
            }
        }
        else{
            $this->addFlash('danger', "Vos accès ne vous permettent pas de faire la soumission");
        }

        $this->entityManager->flush();
//        $this->addFlash('success', "Activité soumise avec succès!");

        return $this->redirect($context->getRequest()->headers->get('referer'));
    }

    #[AdminRoute(path: '/approbation', name: 'admin_activite_approbation')]
    public function ApprouverActivite(AdminContext $context)
    {
        $activite = $context->getEntity()->getInstance();
        $user = $this->getUser();
        $utilisateur = $this->utilisateurRepository->findOneBy(['user' => $user]);
        $datetime = new \DateTimeImmutable();

        if (!$utilisateur){
            $this->addFlash('danger', "Veuillez vous connecter pour approuver les activités");
            return $this->redirect($context->getRequest()->headers->get('referer'));
        }

        // Validation au niveau du district
        if ($this->activiteWorkflow->can($activite, 'valider_district')) {
            $this->activiteWorkflow->apply($activite, 'valider_district');
            $activite->setApprobateurDistrict($utilisateur);
            $activite->setApprobationDistrictAt($datetime);
        }


        $this->entityManager->flush();
        $this->addFlash('success', "Activité validée");
        return $this->redirect($context->getRequest()->headers->get('referer'));
    }

    #[AdminRoute('/validation', name:'admin_activite_validation')]
    public function validerActivite(AdminContext $context)
    {
        $activite = $context->getEntity()->getInstance();
        $user = $this->getUser();
        $utilisateur = $this->utilisateurRepository->findOneBy(['user' => $user]);
        $datetime = new \DateTimeImmutable();

        if (!$utilisateur){
            $this->addFlash('danger', "Veuillez vous connecter pour valider les activités");
            return $this->redirect($context->getRequest()->headers->get('referer'));
        }

        // Validation au niveau du district
        if ($this->activiteWorkflow->can($activite, 'valider_district')) {
            $this->activiteWorkflow->apply($activite, 'valider_district');
            $activite->setApprobateurDistrict($utilisateur);
            $activite->setApprobationDistrictAt($datetime);
        }

        // Validation au niveau de la région
        if ($this->activiteWorkflow->can($activite, 'valider_region')){
            $this->activiteWorkflow->apply($activite, 'valider_region');
            $activite->setApprobateurRegion($utilisateur);
            $activite->setApprobationRegionAt($datetime);
        }

        $this->entityManager->flush();
        $this->addFlash('success', "Activité validée");
        return $this->redirect($context->getRequest()->headers->get('referer'));
    }

    #[AdminRoute('/rejet', name:'admin_activite_rejet')]
    public function rejeterActivite(AdminContext $context)
    {
        $activite = $context->getEntity()->getInstance();
        $user = $this->getUser();
        $utilisateur = $this->utilisateurRepository->findOneBy(['user' => $user]);
        $datetime = new \DateTimeImmutable();

        if (!$utilisateur){
            $this->addFlash('danger', "Veuillez vous connecter pour rejeter les activités");
            return $this->redirect($context->getRequest()->headers->get('referer'));
        }

        // Rejet au niveau du district
        if ($this->activiteWorkflow->can($activite, 'rejeter_district')) {
            $this->activiteWorkflow->apply($activite, 'rejeter_district');
            $activite->setApprobateurDistrict($utilisateur);
            $activite->setApprobationDistrictAt($datetime);
        }

        // Rejet au niveau de la région
        elseif ($this->activiteWorkflow->can($activite, 'rejeter_region')){
            $this->activiteWorkflow->apply($activite, 'rejeter_region');
            $activite->setApprobateurRegion($utilisateur);
            $activite->setApprobationRegionAt($datetime);
        }

        else{
            $this->addFlash('danger', "Impossible de rejeter cette activité");
            return $this->redirect($context->getRequest()->headers->get('referer'));
        }

        $this->entityManager->flush();
        $this->addFlash('success', "Activité rejetée avec succès!");
        return $this->redirect($context->getRequest()->headers->get('referer'));
    }

}
