<?php

namespace App\Controller;

use App\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SitemapController extends AbstractController
{
    public function __construct(private PageRepository $pageRepository)
    {
    }

    #[Route('/sitemap', name: 'app_sitemap', defaults: ["_format" => "xml"])]
    public function index(): Response
    {
        return $this->render('sitemap/index.html.twig');
    }

    private function page()
    {

    }
}
