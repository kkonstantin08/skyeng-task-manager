<?php

namespace App\Service;

use App\Entity\Status;
use App\Repository\StatusRepository;
use App\Repository\TaskRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

final class StatusService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private StatusRepository $statuses,
        private TaskRepository $tasks,
    ) {
    }

    public function create(string $name, string $title): ?Status
    {
        if ($this->statuses->findOneBy(['name' => $name]) !== null) {
            return null;
        }

        $status = new Status($name, $title);
        $this->entityManager->persist($status);

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            return null;
        }

        return $status;
    }

    public function delete(Status $status): bool
    {
        if ($status->getName() === 'new' || $this->tasks->countForStatus($status) > 0) {
            return false;
        }

        $this->entityManager->remove($status);
        $this->entityManager->flush();

        return true;
    }
}
