<?php

declare(strict_types=1);

namespace App\Services\Geo;

use App\Contracts\Repositories\ResponseTemplateRepositoryInterface;
use App\Contracts\Services\Geo\ResponseTemplateServiceInterface;
use App\DTO\Geo\ResponseTemplateData;
use App\Models\ResponseTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ResponseTemplateService implements ResponseTemplateServiceInterface
{
    public function __construct(protected ResponseTemplateRepositoryInterface $responseTemplateRepository) {}

    /**
     * @return Collection<int, ResponseTemplate>
     */
    public function getTemplatesForUser(User $user): Collection
    {
        $key = 'response_templates_user_'.$user->id;

        return Cache::tags(['response_templates'])->remember($key, 86400, function () use ($user): Collection {
            return $this->responseTemplateRepository->getForUser($user);
        });
    }

    public function storeTemplate(ResponseTemplateData $data): ResponseTemplate
    {
        return $this->responseTemplateRepository->store($data);
    }

    public function deleteTemplate(ResponseTemplate $template): void
    {
        $this->responseTemplateRepository->delete($template);
    }
}
