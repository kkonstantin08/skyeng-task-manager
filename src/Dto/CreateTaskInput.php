<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateTaskInput
{
    public function __construct(
        #[Assert\NotBlank(normalizer: 'trim')]
        #[Assert\Type('string')]
        #[Assert\Length(max: 255)]
        public mixed $title,
        #[Assert\Length(max: 5000)]
        #[Assert\Type('string')]
        public mixed $description,
    ) {
    }
}
