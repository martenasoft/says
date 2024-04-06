<?php

namespace App\Repository;

use App\Entity\Interfaces\MenuInterface;
use App\Entity\Interfaces\NodeInterface;
use App\Entity\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class MenuRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private PageRepository $pageRepository
    )
    {
        parent::__construct($registry, Menu::class);
    }

    public function create(NodeInterface $node, ?NodeInterface $parent = null): NodeInterface
    {
        return (new NestedSetsCreateDelete($this->getEntityManager(), Menu::class))->create($node, $parent);
    }

    public function delete(NodeInterface $node, bool $isSafeDelete = false): void
    {
        (new NestedSetsCreateDelete($this->getEntityManager(), Menu::class))->delete($node, $isSafeDelete);
    }

    public function getAllQueryBuilder(?QueryBuilder $queryBuilder = null, string $alias = 'm'): QueryBuilder
    {
        $queryBuilder = $queryBuilder ?? $this->createQueryBuilder($alias);

        $queryBuilder
            ->orderBy("{$alias}.tree", "ASC")
            ->addOrderBy("{$alias}.lft", "ASC");

        return $queryBuilder;
    }

    public function getAllSubItemsQueryBuilder(
        NodeInterface $menu,
        ?QueryBuilder $queryBuilder = null,
        ?int $deep = null,
        string        $alias = 'm'
    ): QueryBuilder
    {
        $queryBuilder = $queryBuilder ?? $this->getAllQueryBuilder(alias: $alias);
        $queryBuilder
            ->andWhere("{$alias}.tree=:tree")->setParameter("tree", $menu->getTree())
            ->andWhere("{$alias}.lft>:lft")->setParameter("lft", $menu->getLft())
            ->andWhere("{$alias}.rgt<:rgt")->setParameter("rgt", $menu->getRgt());

        if ($deep !== null) {
            $queryBuilder
                ->andWhere("{$alias}.lvl<=:lvl")
                ->setParameter('lvl', $menu->getLvl()+$deep)
            ;
        }

        return $queryBuilder;
    }

    public function getAllRootsQueryBuilder(string $alias = 'm'): QueryBuilder
    {
        return $this
            ->createQueryBuilder($alias)
            ->andWhere("{$alias}.lft=:lft")
            ->setParameter("lft", 1);
    }

    public function move(NodeInterface $node, ?NodeInterface $parent): void
    {
        try {
            (new NestedSetsMoveItems($this->getEntityManager(), Menu::class))->move($node, $parent);
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    public function upDown(NodeInterface $node, bool $isUp = true, ?callable $func = null): void
    {
        try {
            (new NestedSetsMoveUpDown($this->getEntityManager(), Menu::class))
                ->upDown($node, $isUp, $func);
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }
//
//    public function findOneByNameQueryBuilder(string $name, string $alias = 'm'): QueryBuilder
//    {
//        return $this
//            ->createQueryBuilder($alias)
//            ->andWhere("{$alias}.name=:name")
//            ->setParameter("name", $name);
//    }
//
//    public function getParentByItemId(int $id): ?NodeInterface
//    {
//        $sql = "SELECT parent_id FROM `" . $this->getClassMetadata()->getTableName() . "` WHERE `id`=:id";
//        $parentId = $this->getEntityManager()->getConnection()->fetchOne($sql, ["id" => $id]);
//        return $this->find($parentId);
//    }

    public function getParentsByItemQueryBuilder(
        NodeInterface $menu,
        ?int          $deep = null,
        ?QueryBuilder $queryBuilder = null,
        bool          $isIncludeCurrentNode = true,
        string        $alias = 'm'
    ): QueryBuilder
    {

        $queryBuilder = $queryBuilder ?? $this->createQueryBuilder($alias);
        $queryBuilder
            ->andWhere("{$alias}.status=:status")
            ->setParameter("status", Menu::STATUS_ACTIVE)
            ->andWhere("{$alias}.tree=:tree")
            ->setParameter('tree', $menu->getTree());


        if ($isIncludeCurrentNode) {
            $queryBuilder
                ->andWhere("{$alias}.lft<=:lft")->setParameter('lft', $menu->getLft())
                ->andWhere("{$alias}.rgt>=:rgt")->setParameter('rgt', $menu->getRgt());
        } else {
            $queryBuilder
                ->andWhere("{$alias}.lft<:lft")->setParameter('lft', $menu->getLft())
                ->andWhere("{$alias}.rgt>:rgt")->setParameter('rgt', $menu->getRgt());
        }

        if ($deep !== null) {
            $queryBuilder
                ->andWhere("{$alias}.lvl=:deep")
                ->setParameter("deep", $menu->getLvl() - $deep);
        }

        return $queryBuilder;
    }

    public function updateUrlInSubElements(NodeInterface $menu, string $oldUrl, string $alias = 'm'): void
    {
        $items = $this
            ->getAllQueryBuilder(alias: $alias)
            ->andWhere("{$alias}.tree=:tree")
            ->setParameter("tree", $menu->getTree())
            ->getQuery()
            ->getResult();

        if (!empty($items)) {
            foreach ($items as $item) {
                $newPath = str_replace($oldUrl, $menu->getSlug(), $item->getPath());
                $item->setPath($newPath);
            }
        }
    }

    public function getAllTrees(string $alias = 'm'): ?array
    {
        return $this
            ->getAllRootsQueryBuilder(alias: $alias)
            ->getQuery()
            ->getResult();
    }


    public function getMenuLength(string $alias = 'm'): int
    {
        return $this
            ->select("COUNT({$alias}.id)")
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getOneParent(?NodeInterface $node): ?NodeInterface
    {
        if ($node === null) {
            return null;
        }
        return $this
            ->getParentsByItemQueryBuilder($node, 1)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function getMenuPath(NodeInterface $menu, string $slider = "/"): string
    {
        $result = '';
        $parentMenuItems =
            $this
                ->getParentsByItemQueryBuilder($menu, isIncludeCurrentNode: false)
                ->getQuery()
                ->getArrayResult();
        foreach ($parentMenuItems as $item) {
            $result .= $slider . $item['slug'];
        }

        return $result;
    }

    public function getWithPagesQueryBuilder(): QueryBuilder
    {
        $pageQueryBuilder = $this->pageRepository->getAllQueryBuilder();
        $pageQueryBuilder->innerJoin("p.menu", "m")->addSelect("m");
        $this->getAllQueryBuilder($pageQueryBuilder);
        return $pageQueryBuilder;
    }
}
