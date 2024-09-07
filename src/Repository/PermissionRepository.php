<?php

namespace App\Repository;

use App\Entity\Permission;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class PermissionRepository extends ServiceEntityRepository
{
    public const ALIAS = 'perm';
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Permission::class);
    }

    public function getAllQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        return $queryBuilder;
    }

    public function findOneByRoute(string $route): ?Permission
    {
        return $this
            ->getAllQueryBuilder()
            ->andWhere(self::ALIAS.".route=:route")
            ->setParameter("route", $route)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getUserRoutesQueryBuilder(string $route): QueryBuilder
    {
        return $this
            ->getAllQueryBuilder()
            ->andWhere(self::ALIAS.'.route=:role')
            ->setParameter('role', $route)
            ;
    }

    public function hasUserRoute(User $user, string $route): bool
    {
        $result = $this->getUserRoutesQueryBuilder($route)->getQuery()->getOneOrNullResult();

        if (empty($result)) {
            return true;
        }

        $result = $this
            ->getAllQueryBuilder()
            ->select(sprintf("COUNT(%s.id)", self::ALIAS))
            ->leftJoin(self::ALIAS.'.users', 'users')
            ->leftJoin(self::ALIAS.'.roles', 'roles')
            ->where(self::ALIAS.".id=:id")
            ->andWhere("users=:user OR roles.name IN (:roles)")
            ->setParameter("id", $result)
            ->setParameter("user", $user)
            ->setParameter("roles", $user->getRoles())
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return !empty($result);
    }


    public function saveCount(Permission $permission)
    {
        $queryBuilder = $this
            ->getAllQueryBuilder()
            ->select("COUNT(u.id) as uCount, COUNT(r.id) as rCount")
            ->leftJoin(self::ALIAS.".users", "u")
            ->leftJoin(self::ALIAS.".roles", "r")
            ->andWhere(self::ALIAS.".id=:id")
            ->setParameter("id", $permission->getId());

        $result = $queryBuilder->getQuery()->getOneOrNullResult() ?? ['uCount' => 0, 'rCount' => 0];
        $permission
            ->setCountOfRoles($result['rCount'])
            ->setCountOfUsers($result['uCount'])
        ;
    }
}
