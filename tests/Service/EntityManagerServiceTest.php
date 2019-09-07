<?php

namespace App\Tests\Service;

use App\Service\EntityManagerService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Doctrine\DBAL\Connection;

class EntityManagerServiceTest extends TestCase
{
    /**
     * @var EntityManagerService
     */
    private $entityService;

    /**
     * @var \MockObjectTest
     */
    private $entityMgrI;

    /**
     * Executed for each test method
     */
    protected function setUp(): void
    {
        $this->entityMgrI = $this->createMock(EntityManagerInterface::class);
        $this->entityService = new EntityManagerService($this->entityMgrI);
    }

    public function testBeginTransaction()
    {
        $this->entityMgrI
            ->expects($this->once())
            ->method('beginTransaction');

        $this->entityService->beginTransaction();
    }

    public function testCommitTransaction()
    {
        $this->entityMgrI
            ->expects($this->once())
            ->method('flush');

        $this->entityMgrI
            ->expects($this->once())
            ->method('commit');

        $this->entityService->commitTransaction();
    }

    public function testSaveEntityWithFlush()
    {
        $entity = new \stdClass();

        $this->entityMgrI
            ->expects($this->once())
            ->method('persist')
            ->with($this->equalTo($entity));

        $this->entityMgrI
            ->expects($this->once())
            ->method('flush');

        $this->entityService->saveEntity($entity, true);
    }

    public function testSaveEntityWithoutFlush()
    {
        $entity = new \stdClass();

        $this->entityMgrI
            ->expects($this->once())
            ->method('persist')
            ->with($this->equalTo($entity));

        $this->entityMgrI
            ->expects($this->exactly(0))
            ->method('flush');

        $this->entityService->saveEntity($entity, false);
    }

    public function testRollbackTransactionWithoutTransactions()
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->once())
            ->method('getTransactionNestingLevel')
            ->willReturn(0);

        $this->entityMgrI
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);

        $this->entityMgrI
            ->expects($this->exactly(0))
            ->method('rollback');

        $this->entityService->rollbackTransaction();
    }

    public function testRollbackTransactionWithTransactions()
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->once())
            ->method('getTransactionNestingLevel')
            ->willReturn(1);

        $this->entityMgrI
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);

        $this->entityMgrI
            ->expects($this->once())
            ->method('rollback');

        $this->entityService->rollbackTransaction();
    }
}
