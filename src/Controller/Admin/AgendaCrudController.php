<?php

namespace App\Controller\Admin;

use App\Entity\Activite;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Factory\EntityFactory;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use phpDocumentor\Reflection\Types\Static_;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AgendaCrudController extends AbstractCrudController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

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
        // Création e l'action Export Excel
        $exportExcel = Action::new('exportExcel', 'Export Excel', 'fa fa-file-excel')
            ->linkToCrudAction('exportExcel')
            ->createAsGlobalAction()
            ->setCssClass('btn btn-success')
            ;

        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $exportExcel)
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

    public function exportExcel(AdminContext $context): StreamedResponse
    {
        // Récupération directe via le repository
//        $entityManager = $this->entity;
        $repository = $this->entityManager->getRepository(Activite::class);

        // Créer le QueryBuilder manuellement avec les mêmes filtres
        $queryBuilder = $repository->createQueryBuilder('activite')
            ->andWhere('activite.statut = :statut')
            ->andWhere('activite.dateDebut >= :today')
            ->setParameter('today', new \DateTime('now'))
            ->setParameter('statut', 'validee')
            ->orderBy('activite.dateDebut', 'ASC')
            ->addOrderBy('activite.dateFin', 'ASC');

        $activites = $queryBuilder->getQuery()->getResult();

        // 4. Génération du tableur
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Définition des colonnes (Tous les champs de l'entité Activite)
        $headers = [
            'A1' => 'Référence', 'B1' => 'Dénomination', 'C1' => 'Lieu',
            'D1' => 'Date Début', 'E1' => 'Date Fin', 'F1' => 'Objectif',
            'G1' => 'Contenu', 'H1' => 'Cibles', 'I1' => 'Responsable',
            'J1' => 'Statut', 'K1' => 'Instance', 'L1' => 'Auteur'
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        $row = 2;
        foreach ($activites as $activite) {
            $sheet->setCellValue('A' . $row, $activite->getReference());
            $sheet->setCellValue('B' . $row, $activite->getDenomination());
            $sheet->setCellValue('C' . $row, $activite->getLieu());
            $sheet->setCellValue('D' . $row, $activite->getDateDebut()?->format('d/m/Y'));
            $sheet->setCellValue('E' . $row, $activite->getDateFin()?->format('d/m/Y'));
            $sheet->setCellValue('F' . $row, strip_tags($activite->getObjectif()));
            $sheet->setCellValue('G' . $row, strip_tags($activite->getContenu()));
            $sheet->setCellValue('H' . $row, is_array($activite->getCible()) ? implode(', ', $activite->getCible()) : '');
            $sheet->setCellValue('I' . $row, $activite->getResponsable());
            $sheet->setCellValue('J' . $row, $activite->getStatut());
            $sheet->setCellValue('K' . $row, (string) $activite->getInstance());
            $sheet->setCellValue('L' . $row, $activite->getAuteur() ? $activite->getAuteur()->getNom().' '.$activite->getAuteur()->getPrenom() : '');
            $row++;
        }

        $writer = new Xlsx($spreadsheet);

        return new StreamedResponse(function() use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => ResponseHeaderBag::DISPOSITION_ATTACHMENT . '; filename="activites_validees_2025-2026.xlsx"',
        ]);
    }

}
