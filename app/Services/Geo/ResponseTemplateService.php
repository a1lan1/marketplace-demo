<?php

declare(strict_types=1);

namespace App\Services\Geo;

use App\Contracts\Services\Geo\ResponseTemplateServiceInterface;
use App\DTO\Geo\ResponseTemplateData;
use App\Models\ResponseTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ResponseTemplateService implements ResponseTemplateServiceInterface
{
    /**
     * @return Collection<int, ResponseTemplate>
     */
    public function getTemplatesForUser(User $user): Collection
    {
        return $user->responseTemplates()->latest()->get();
    }

    public function storeTemplate(ResponseTemplateData $data): ResponseTemplate
    {
        return ResponseTemplate::updateOrCreate(
            ['id' => $data->id],
            [
                'seller_id' => $data->sellerId,
                'title' => $data->title,
                'body' => $data->body,
            ]
        );
    }

    public function deleteTemplate(ResponseTemplate $template): void
    {
        $template->delete();
    }
}
