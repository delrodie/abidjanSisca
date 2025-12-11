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

            FormField::addColumn('col-md-6 mt-3 mt-3 mt-md-5'),
            FormField::addPanel('Information du compte'),
            FormField::addPanel()
                ->setFormType(UserEmbeddedType::class),

            FormField::addColumn('col-md-6 mt-3 mt-md-5'),
            FormField::addPanel('Affectation'),
            AssociationField::new('instance')
                ->setLabel('Instance'),
            AssociationField::new('organe')
                ->setLabel('Organe'),
            EmailField::new('email')
                ->setLabel('Adresse email'),
            BooleanField::new('actif')
                ->setLabel('Actif'),


            DateTimeField::new('createdAt')
                ->setLabel('Crée le ')
                ->hideOnForm(),
        ];
    }

}
