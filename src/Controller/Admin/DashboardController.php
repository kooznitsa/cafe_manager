<?php

namespace App\Controller\Admin;

use App\Entity\{Category, Dish, Product, Recipe, User};
use EasyCorp\Bundle\EasyAdminBundle\Config\{Dashboard, MenuItem};
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin{_locale}', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Панель администратора')
            ->setFaviconPath('favicon.svg')
            ->renderContentMaximized()
            ->setDefaultColorScheme('light')
            ->setLocales([
                'ru' => '🇷🇺 Русский',
                'en' => '🇬🇧 English',
            ])
            ;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Пользователи', 'fas fa-user', User::class);
        yield MenuItem::linkToCrud('Категории', 'fas fa-tags', Category::class);
        yield MenuItem::linkToCrud('Блюда', 'fas fa-list', Dish::class);
        yield MenuItem::linkToCrud('Продукты', 'fas fa-list', Product::class);
        yield MenuItem::linkToCrud('Рецепты', 'fas fa-list', Recipe::class);
    }
}
