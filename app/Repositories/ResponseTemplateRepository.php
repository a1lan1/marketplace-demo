<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\ResponseTemplateRepositoryInterface;
use App\DTO\Geo\ResponseTemplateData;
use App\Models\ResponseTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ResponseTemplateRepository implements ResponseTemplateRepositoryInterface
{
    public function getForUser(User $user): Collection
    {
        return $user->responseTemplates()
            ->select(['id', 'seller_id', 'title', 'body', 'created_at'])
            ->latest()
            ->get();
    }

    public function store(ResponseTemplateData $data): ResponseTemplate
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

    public function delete(ResponseTemplate $template): void
    {
        $template->delete();
    }
}
