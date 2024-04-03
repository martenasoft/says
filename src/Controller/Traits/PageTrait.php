<?php

namespace App\Controller\Traits;

use App\Entity\Page;
use App\Entity\User;
use App\Repository\PageRepository;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Monolog\DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;

trait PageTrait
{
    private function getPagination(
        PageRepository     $pageRepository,
        PaginatorInterface $paginator,
        Request            $request,
        int                $status = Page::STATUS_ACTIVE,
        ?Page $page = null,
        ?QueryBuilder $queryBuilder = null
    ): PaginationInterface
    {
        $queryBuilder = $queryBuilder ?? $this->getItemsQueryBuilder($pageRepository);
        $user = $this->getUser();

        if (!empty($user) && !in_array(User::ADMIN_ROLE, $user->getRoles())) {
            $queryBuilder
                ->andWhere("p.status=:status")
                ->setParameter('status', $status)
                ->andWhere("p.publicAt<=:now")
                ->setParameter("now", (new DateTimeImmutable('now')));
        }

        if ($page) {
            $queryBuilder
                ->andWhere("p.parent=:parent")
                ->setParameter("parent", $page);
        }

        //
        return $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1)
        );
    }

    private function getItemsQueryBuilder(PageRepository $pageRepository): QueryBuilder
    {
        return $pageRepository
            ->getAllQueryBuilder()
            ->addOrderBy('p.position', "ASC")
            ->addOrderBy("p.publicAt", "DESC")
            ;

    }
}