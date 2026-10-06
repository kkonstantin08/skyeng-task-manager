<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class ChangeTaskStatusInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        public mixed $status,
    ) {
    }
}
