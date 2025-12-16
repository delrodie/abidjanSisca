<?php

namespace App\Controller\Admin;

use App\Entity\Organe;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class OrganeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Organe::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des organes')
            ->setPageTitle('new', "Enregistrement d'un nouvel organe")
            ->setPageTitle('edit', fn(Organe $organe) => sprintf('Modification de <b>%s</b>', $organe->getNom()))

            ->setAutofocusSearch(true)
            ;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addColumn('col-md-6 offset-md-3 mt-5'),
            IdField::new('id')->hideOnForm(),
            TextField::new('nom')
                ->setLabel("Nom de l'organe")
                ->setRequired(true)
                ->setFormTypeOptions([
                    'attr' => ['autocomplete' => "off"]
                ]),
            ChoiceField::new('role')
                ->setChoices([
                    'ROLE_ADMIN' => 'ROLE_ADMIN',
                    'ROLE_PSEUDO_ADMIN' => 'ROLE_PSEUDO_ADMIN',
                    'ROLE_AT' => 'ROLE_AT',
                    'ROLE_REGION' => 'ROLE_REGION',
                    'ROLE_DISTRICT' => 'ROLE_DISTRICT',
                    'ROLE_GROUPE' => 'ROLE_GROUPE',
                    'ROLE_USER' => 'ROLE_USER',
                ])
        ];
    }

}
