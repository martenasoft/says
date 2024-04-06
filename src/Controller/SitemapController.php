<?php

namespace App\Controller;

use App\Repository\PageRepository;
use App\Service\MenuService;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SitemapController extends AbstractController
{
    public function __construct(private MenuService $menuService)
    {
    }

    #[Route('/sitemap', name: 'app_sitemap', defaults: ["_format" => "xml"], methods: ['GET'] )]
    public function page(): Response
    {

        $items = $this->menuService->getAllItems(MenuService::ALL_MENU_CACHE_KEY, function (QueryBuilder $queryBuilder) {
            // Todo create field is on site map on the menu entity
        });

        return $this->render('sitemap/page.xml.twig', [
            'items' => $items
        ]);
    }
}
