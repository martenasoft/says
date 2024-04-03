<?php

namespace App\Repository;

use App\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

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

    public function getAllQueryBuilder(string $alias = 'p'): QueryBuilder
    {
        return $this
            ->createQueryBuilder($alias)
            ->leftJoin("p.parent", "pp")
            ->addSelect("pp")
            ;
    }

    public function breadCrumbs(Page $page): array
    {
        if (!$page->getParent()) {
            return [];
        }
        $result = $this
            ->createQueryBuilder('p')
            ->andWhere("p.id=:parent")
            ->leftJoin("p.parent", "pp")
            ->addSelect("pp")
            ->setParameter("parent", $page->getParent()->getId())
            ->getQuery()
            ->getResult()
        ;

        foreach ($result as $item) {
            $result = array_merge($result, $this->breadCrumbs($item));
        }

        return $result ?? [];
    }

}
