<?php

namespace App\Controller\Admin;

use App\Entity\Participant;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        //return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        return $this->render('admin/index.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Enisortir');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::section('Blog');
        yield MenuItem::linkToUrl('Home', 'fa fa-home', '/');

        yield MenuItem::section('User');
        yield MenuItem::subMenu('Participant', 'fas fa-bar')->setSubItems([
            MenuItem::linkToCrud('Create Participant', 'fas fa-plus-circle', Participant::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud('Show Participant', 'fas fa-eye', Participant::class)
        ]);
    }
}
