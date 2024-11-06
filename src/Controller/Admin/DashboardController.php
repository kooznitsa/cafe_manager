<?php

namespace App\Controller\Admin;

use App\Entity\{Category, Dish, Order, Product, Purchase, Recipe, User};
use App\Service\OrderBuilderService;
use EasyCorp\Bundle\EasyAdminBundle\Config\{Dashboard, MenuItem};
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class DashboardController extends AbstractDashboardController
{
    private const CHART_COLOR = 'rgb(139, 69, 19)';
    private const CHART_MAX_SUM = 1000;

    public function __construct(
        private readonly ChartBuilderInterface $chartBuilder,
        private readonly OrderBuilderService $orderBuilderService,
    ) {
    }

    #[Route('/admin{_locale}', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'chart' => $this->createChart(),
        ]);
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
        yield MenuItem::linkToDashboard('График продаж', 'fa fa-home');
        yield MenuItem::linkToCrud('Пользователи', 'fas fa-user', User::class);
        yield MenuItem::linkToCrud('Категории', 'fas fa-tags', Category::class);
        yield MenuItem::linkToCrud('Блюда', 'fas fa-list', Dish::class);
        yield MenuItem::linkToCrud('Продукты', 'fas fa-list', Product::class);
        yield MenuItem::linkToCrud('Рецепты', 'fas fa-list', Recipe::class);
        yield MenuItem::linkToCrud('Закупки', 'fas fa-list', Purchase::class);
        yield MenuItem::linkToCrud('Заказы', 'fas fa-list', Order::class);
    }

    private function createChart(): Chart
    {
        [$dates, $sums] = $this->orderBuilderService->getChartData();

        return $this->chartBuilder->createChart(Chart::TYPE_LINE)
            ->setData([
                'labels' => $dates,
                'datasets' => [
                    [
                        'label' => 'Продажи в рублях',
                        'backgroundColor' => self::CHART_COLOR,
                        'borderColor' => self::CHART_COLOR,
                        'data' => $sums,
                    ],
                ],
            ])
            ->setOptions([
                'scales' => [
                    'y' => [
                        'suggestedMin' => 0,
                        'suggestedMax' => self::CHART_MAX_SUM,
                    ],
                ],
            ])
            ;
    }
}
