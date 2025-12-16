<?php

namespace App\Controller\Admin;

use App\Entity\Activite;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use phpDocumentor\Reflection\Types\Static_;

class AgendaCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Activite::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->overrideTemplate('crud/detail', 'admin/agenda_detail.html.twig')
            ->setPaginatorPageSize(10)
            ;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
//            IdField::new('id'),
            TextField::new('reference'),
            AssociationField::new('instance'),
            TextField::new('denomination', "Dénomination"),
            TextField::new('lieu', "Lieu d'activité"),
            DateField::new('dateDebut', "Date début"),
            DateField::new('dateFin', "Date fin"),
            ArrayField::new('cible', 'Cibles')
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
//        $detail = Action::new('Details')
//            ->setIcon('fa fa-eye')
//            ->setLabel(false);

        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_DETAIL, Action::INDEX,
                static fn(Action $action) => $action
                    ->setIcon('fa fa-arrow-left')
                    ->setLabel('Retour à la liste')
            )
            ;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $rootAlias = $queryBuilder->getRootAliases()[0];

        return $queryBuilder
                ->andWhere("{$rootAlias}.statut = (:statut)")
                ->andWhere("{$rootAlias}.dateDebut >= (:today)")
                ->setParameter('today', new \DateTime('now'))
                ->setParameter('statut', 'validee')
                ->orderBy("{$rootAlias}.dateDebut", "ASC")
                ->addOrderBy("{$rootAlias}.dateFin", "ASC")
            ;
    }

}
