<?php

namespace App\Controller\Admin;

use App\Entity\Instance;
use App\Entity\Organe;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
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
            ->setTitle('SISCA');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');

        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);

        if ($this->isGranted('ROLE_ADMIN')){

            yield MenuItem::section('Paramètre');
            yield MenuItem::subMenu('Instances', 'fas fa-layer-group')->setSubItems([
                MenuItem::linkToCrud('Liste des instances', 'fas fa-list', Instance::class),
                MenuItem::linkToRoute('Importer des instances', 'fas fa-file-import', 'admin_import_excel_instances')
            ]);
            yield MenuItem::linkToCrud('Organes', 'fas fa-layer-group', Organe::class);

            yield MenuItem::section('Sécurité');
            yield MenuItem::linkToCrud('Utilisateur', 'fa fa-users', User::class);

        }

    }


}
