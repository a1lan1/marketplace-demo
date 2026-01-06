<?php

use App\Models\ResponseTemplate;
use App\Models\User;

it('belongs to a seller', function (): void {
    $seller = User::factory()->create();
    $template = ResponseTemplate::factory()->create(['seller_id' => $seller->id]);

    expect($template->seller)->toBeInstanceOf(User::class)
        ->and($template->seller->id)->toBe($seller->id);
});
