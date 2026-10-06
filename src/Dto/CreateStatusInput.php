<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateStatusInput
{
    public function __construct(
        #[Assert\NotBlank(normalizer: 'trim')]
        #[Assert\Type('string')]
        #[Assert\Length(max: 50)]
        #[Assert\Regex('/^[a-z][a-z0-9_]*$/')]
        public mixed $name,
        #[Assert\NotBlank(normalizer: 'trim')]
        #[Assert\Type('string')]
        #[Assert\Length(max: 100)]
        public mixed $title,
    ) {
    }
}
