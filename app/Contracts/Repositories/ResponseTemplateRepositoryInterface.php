<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DTO\Geo\ResponseTemplateData;
use App\Models\ResponseTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface ResponseTemplateRepositoryInterface
{
    public function getForUser(User $user): Collection;

    public function store(ResponseTemplateData $data): ResponseTemplate;

    public function delete(ResponseTemplate $template): void;
}
