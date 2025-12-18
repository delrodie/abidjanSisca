<?php

namespace App\Controller\Admin;

use App\Entity\Activite;
use App\Entity\Instance;
use App\Entity\Organe;
use App\Entity\User;
use App\Entity\Utilisateur;
use App\Enum\InstanceType;
use App\Repository\ActiviteRepository;
use App\Repository\InstanceRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[AdminDashboard(routePath: '/admin', routeName: 'admin_dashboard')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly ActiviteRepository $activiteRepository,
        private readonly InstanceRepository $instanceRepository,
        private readonly ChartBuilderInterface $chartBuilder,
    )
    {
    }

    public function index(): Response
    {
        $activites = $this->activiteRepository->findByStatut('validee');

        $aujourdhui = new \DateTime('today'); //dd($aujourdhui);
        $activiteRegionale=0; $activiteDistrict=0; $activiteGroupe=0; $nonDefini=0;
        $activiteAVenir=0; $activitePasee=0;

        foreach ($activites as $activite) {
            $instanceType=$activite->getInstance()->getType();
            $dateDebutActivite = $activite->getDateDebut();
            $dateFinActivite = $activite->getDateFin();

            if ($instanceType === InstanceType::GROUPE){
                $activiteGroupe++;
            }elseif ($instanceType === InstanceType::DISTRICT){
                $activiteDistrict++;
            }elseif($instanceType === InstanceType::REGION){
                $activiteRegionale++;
            }else{
                $nonDefini++;
            }

            if ($dateDebutActivite >= $aujourdhui) {
                $activiteAVenir++;
            }
            if ($dateFinActivite < $aujourdhui) {
                $activitePasee++;
            }
        }

          return $this->render('admin/dashboard.html.twig', [
              'totalActivite' => count($activites),
              'activiteRegionale' => $activiteRegionale,
              'activiteDistrict' => $activiteDistrict,
              'activiteGroupe' => $activiteGroupe,
              'nonDefini' => $nonDefini,
              'activiteAVenir' => $activiteAVenir,
              'activitePassee' => $activitePasee,
              'chart' => $this->activiteChartJs()
          ]);
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

        yield MenuItem::linkToCrud('Agenda', 'fa fa-calendar', Activite::class)
            ->setController(AgendaCrudController::class);

        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
        // <i class="fa-solid fa-tents"></i>

        yield MenuItem::section('Gestion');
        yield MenuItem::linkToCrud('Activités', 'fa-solid fa-tents', Activite::class)
            ->setController(ActiviteCrudController::class);

        if ($this->isGranted('ROLE_AT')){
            yield MenuItem::section('Paramètre');
            yield MenuItem::subMenu('Instances', 'fa-regular fa-building')->setSubItems([
                MenuItem::linkToCrud('Liste des instances', 'fas fa-list', Instance::class),
                MenuItem::linkToRoute('Importer des instances', 'fas fa-file-import', 'admin_import_excel_instances')
                            ->setPermission('ROLE_ADMIN')
            ]);
            yield MenuItem::linkToCrud('Organes', 'fa-solid fa-chart-diagram', Organe::class)
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

    public function configureAssets(): Assets
    {
        return parent::configureAssets()
            ->addAssetMapperEntry('app')
            ;
    }

    public function activiteChartJs(): Chart
    {
        $region = $this->instanceRepository->findOneBy(['nom' => 'Abidjan']);
        $data = $this->activiteRepository->countActivitiesByMonthForRegion($region);

        // 3. Formater pour Chart.js
        $labels = [];
        $dataset = [];
        foreach ($data as $row) {
            $labels[] = $row['month']; // Format YYYY-MM
            $dataset[] = $row['count'];
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Activités par mois',
                    'backgroundColor' => 'rgb(61, 40, 114)',
                    'data' => $dataset,
                ],
            ],
        ]);

        $chart->setOptions([
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ]);

        return $chart;
    }


}
