<?php

namespace App\Controller\Admin;

use App\Entity\Organe;
use App\Entity\Utilisateur;
use App\Form\UserEmbeddedType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UtilisateurCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Utilisateur::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des comptes')
            ->setPageTitle('new', "Enregistrement d'un nouveau compte")
            ->setPageTitle('edit', fn(Utilisateur $utilisateur) => sprintf('Modification de <b>%s</b>', $utilisateur->getInstance()))

            ->setAutofocusSearch(true)
            ;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            FormField::addColumn('col-md-6 mt-3 mt-md-5'),
            FormField::addPanel('Identité'),
            AssociationField::new('instance')
                ->setLabel('Instance')
                ->setFormTypeOptions([
                    'attr' => ['autocomplete' => 'off']
                ])
                ->setRequired(true),
            TextField::new('nom')
                ->setLabel('Nom')
                ->setFormTypeOptions([
                    'attr' => ['autocomplete' => 'off']
                ])
                ->setRequired(true),
            TextField::new('prenom')
                ->setLabel('Prenoms')
                ->setRequired(true),
            EmailField::new('email')
                ->setLabel('Adresse email')
                ->setFormTypeOptions([
                    'attr' => ['autocomplete' => 'off']
                ]),


            FormField::addColumn('col-md-6 mt-3 mt-3 mt-md-5'),

            FormField::addPanel('Information du compte'),
            AssociationField::new('organe')
                ->setLabel('Organe')
                ->setRequired(true),
            TextField::new('username')
                ->setLabel('Nom d\'utilisateur')
                ->setFormTypeOptions([
                    'attr' => ['autocomplete' => 'off']
                ])
                ->setRequired(true),
            TextField::new('userpass')
                ->setLabel('Mot de passe')
                ->onlyOnForms()
                ->setPermission('ROLE_AT')
                ->setFormTypeOptions([
                    'attr' => ['autocomplete' => 'off']
                ]),
            BooleanField::new('actif')
                ->setLabel('Actif'),


            DateTimeField::new('createdAt')
                ->setLabel('Crée le ')
                ->hideOnForm(),
        ];
    }

}
