<?php

namespace App\Repository;

use App\Entity\Status;
use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function countForStatus(Status $status): int
    {
        return (int) $this->createQueryBuilder('task')
            ->select('COUNT(task.id)')
            ->andWhere('task.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findByStatusName(string $statusName): array
    {
        return $this->createQueryBuilder('task')
            ->join('task.status', 'status')
            ->andWhere('status.name = :name')
            ->setParameter('name', $statusName)
            ->orderBy('task.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
