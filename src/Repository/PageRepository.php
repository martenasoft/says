<?php

namespace App\Repository;

use App\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @extends ServiceEntityRepository<Page>
 *
 * @method Page|null find($id, $lockMode = null, $lockVersion = null)
 * @method Page|null findOneBy(array $criteria, array $orderBy = null)
 * @method Page[]    findAll()
 * @method Page[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class);
    }

    public function getOneBySlugQueryBuilder(string $slug, ?int $notType = Page::CONTROLLER_ROUTE_TYPE, string $alias = 'p'): QueryBuilder
    {
        $queryBuilder = $this
            ->getAllQueryBuilder($alias)
            ->andWhere("p.slug=:slug")
            ->setParameter("slug", $slug)
           ;

        if ($notType !== null) {
            $queryBuilder
                ->andWhere("p.type != :controllerRouteType")
                ->setParameter('controllerRouteType', Page::CONTROLLER_ROUTE_TYPE)
            ;
        }

        return $queryBuilder;
    }

    public function getOneById(int $id, string $alias = 'p'): Page
    {
        $page = $this
            ->getAllQueryBuilder($alias)
            ->andWhere("{$alias}.id=:id")
            ->setParameter("id", $id)
            ->getQuery()
            ->getOneOrNullResult();
        ;

        if (!$page) {
            throw new NotFoundHttpException("Page [$id] not found");
        }

        return $page;
    }

    public function getAllQueryBuilder(string $alias = 'p'): QueryBuilder
    {
        return $this
            ->createQueryBuilder($alias)
            ->leftJoin("p.menu", "pm")
            ->addSelect("pm")
            ;
    }

    public function breadCrumbs(Page $page): array
    {
        return [];
    }

}
