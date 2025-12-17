<?php

declare(strict_types=1);

namespace App\DTO;

use App\Models\User;
use Illuminate\Http\UploadedFile;

class ProductDTO
{
    public function __construct(
        public User $user,
        public string $name,
        public ?string $description,
        public int $price,
        public int $stock,
        public ?UploadedFile $coverImage = null,
        public ?int $productId = null
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
        ];
    }
}
