<?php

declare(strict_types=1);

namespace App\Contracts\Services\Geo;

use App\DTO\Geo\ResponseTemplateData;
use App\Models\ResponseTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface ResponseTemplateServiceInterface
{
    /**
     * @return Collection<int, ResponseTemplate>
     */
    public function getTemplatesForUser(User $user): Collection;

    public function storeTemplate(ResponseTemplateData $data): ResponseTemplate;

    public function deleteTemplate(ResponseTemplate $template): void;
}
