<?php

namespace App\Repository;

use App\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class RoleRepository extends ServiceEntityRepository
{
    public const ALIAS = 'r';
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    public function findByName(string $name): ?Role
    {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $queryBuilder
            ->andWhere(self::ALIAS.'.name LIKE :role_admin')
            ->setParameter("role_admin", $name)
            ;
        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function findAllByNames(array $names): ?array
    {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $queryBuilder
            ->andWhere(self::ALIAS.'.name IN (:roles)')
            ->setParameter("roles", $names)
        ;
        return $queryBuilder->getQuery()->getResult();
    }

    public function save(Role $role, bool $isFlush = false): void
    {
        $this->getEntityManager()->persist($role);
        if ($isFlush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getAllQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder(self::ALIAS);
    }

}
