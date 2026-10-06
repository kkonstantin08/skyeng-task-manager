<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ChangeTaskStatusInput
{
    public function __construct(
        #[Assert\NotBlank(normalizer: 'trim')]
        #[Assert\Type('string')]
        #[Assert\Length(max: 50)]
        #[Assert\Regex('/^[a-z][a-z0-9_]*$/')]
        public mixed $status,
    ) {
    }
}
