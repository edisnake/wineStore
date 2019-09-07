<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class EntityManagerService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityMgrI;

    /**
     * EntityManager constructor.
     * @param EntityManagerInterface $entityMgrI
     */
    public function __construct(EntityManagerInterface $entityMgrI)
    {
        $this->entityMgrI = $entityMgrI;
    }

    /**
     * Saves an entity into the database
     * @param $entity
     * @param bool $flush
     */
    public function saveEntity($entity, bool $flush = false): void
    {
        $this->entityMgrI->persist($entity);
        if ($flush) {
            $this->entityMgrI->flush();
        }
    }

    /**
     * begins a transaction into the database
     */
    public function beginTransaction(): void
    {
        $this->entityMgrI->beginTransaction();
    }

    /**
     * Commits the current transaction into the database
     */
    public function commitTransaction(): void
    {
        $this->entityMgrI->flush();
        $this->entityMgrI->commit();
    }

    /**
     * Rollback the current transaction from the database
     */
    public function rollbackTransaction(): void
    {
        // If there is an active transaction, then revert it
        if ($this->entityMgrI->getConnection()->getTransactionNestingLevel()) {
            $this->entityMgrI->rollback();
        }
    }
}
