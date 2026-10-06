<?php

namespace App\Service;

use App\Entity\Task;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;

final class TaskService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private StatusRepository $statuses,
    ) {
    }

    public function create(string $title, ?string $description): ?Task
    {
        $status = $this->statuses->findOneBy(['name' => 'new']);
        if ($status === null) {
            return null;
        }

        $task = new Task($title, $description, $status);
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $task;
    }

    public function changeStatus(Task $task, string $statusName): bool
    {
        $status = $this->statuses->findOneBy(['name' => $statusName]);
        if ($status === null) {
            return false;
        }

        $task->changeStatus($status);
        $this->entityManager->flush();

        return true;
    }

    public function delete(Task $task): void
    {
        $this->entityManager->remove($task);
        $this->entityManager->flush();
    }
}
