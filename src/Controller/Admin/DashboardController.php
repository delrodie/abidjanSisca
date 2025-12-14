<?php

namespace App\Controller\Admin;

use App\Entity\Instance;
use App\Entity\Organe;
use App\Entity\User;
use App\Entity\Utilisateur;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin_dashboard')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
//        return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // 1.1) If you have enabled the "pretty URLs" feature:
        // return $this->redirectToRoute('admin_user_index');
        //
        // 1.2) Same example but using the "ugly URLs" that were used in previous EasyAdmin versions:
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirectToRoute('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
         return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('
                    <div class="d-flex align-items-center">
                        <img src="logo.png" style="width: 50px;" class="img-fluid">
                        <h3 class="fw-bold mt-2" style="color: #3D2872">SISCA</h3>
                    </div>
                ')
            ->setFaviconPath('logo.png')
            ;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');

        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class); <i class=""></i>

        if ($this->isGranted('ROLE_AT')){

            yield MenuItem::section('Paramètre');
            yield MenuItem::subMenu('Instances', 'fas fa-layer-group')->setSubItems([
                MenuItem::linkToCrud('Liste des instances', 'fas fa-list', Instance::class),
                MenuItem::linkToRoute('Importer des instances', 'fas fa-file-import', 'admin_import_excel_instances')
                            ->setPermission('ROLE_ADMIN')
            ]);
            yield MenuItem::linkToCrud('Organes', 'fas fa-layer-group', Organe::class)
                            ->setPermission('ROLE_ADMIN');

            yield MenuItem::section('Sécurité');
            yield MenuItem::subMenu('Comptes', 'fa-solid fa-user-lock')->setSubItems([
                MenuItem::linkToCrud('Liste des comptes', 'fas fa-list', Utilisateur::class),
                MenuItem::linkToRoute('Générer des comptes', 'fa-solid fa-user-clock', 'admin_generate_compte')
                            ->setPermission('ROLE_SUPER_ADMIN')
            ]);
            yield MenuItem::linkToCrud('Users', 'fa-solid fa-user-shield', User::class)
                ->setPermission('ROLE_SUPER_ADMIN');

        }

    }


}
