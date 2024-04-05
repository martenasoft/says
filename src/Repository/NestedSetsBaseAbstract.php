<?php

namespace App\Repository;

use App\Entity\Interfaces\NodeInterface;
use App\Exceptions\NestedSetsException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use function Symfony\Component\String\s;

abstract class NestedSetsBaseAbstract
{
    private EntityManagerInterface $entityManager;
    private string $tableName;
    private string $entityClassName;

    protected $alias = 'ns';

    public function __construct(EntityManagerInterface $entityManager, ?string $entityClassName = null)
    {
        $this->entityManager = $entityManager;
        if (!empty($entityClassName)) {
            $this->setEntityClassName($entityClassName);
        }
    }

    public function setEntityClassName(string $entityClassName): void
    {
        if (!is_subclass_of(new $entityClassName(), NodeInterface::class)) {
            throw new NestedSetsException(
                sprintf(
                    "The class %s not implement interface %s",
                    $entityClassName,
                    NodeInterface::class
                )
            );
        }
        $this->entityClassName = $entityClassName;
        $this->tableName = $this->getEntityManager()->getClassMetadata($entityClassName)->getTableName();
    }

    protected function sqlToEntity($sql)
    {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata($this->entityClassName, 'n');
        $result = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        return $result->getOneOrNullResult();
    }
    protected function getTableName(): string
    {
        return $this->tableName;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }
}