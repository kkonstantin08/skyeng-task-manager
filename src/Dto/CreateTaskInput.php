<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateTaskInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        #[Assert\Length(max: 255)]
        public mixed $title,
        #[Assert\Type('string')]
        #[Assert\Length(max: 5000)]
        public mixed $description,
    ) {
    }
}
