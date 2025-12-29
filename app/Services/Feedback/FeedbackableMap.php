<?php

declare(strict_types=1);

namespace App\Services\Feedback;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class FeedbackableMap
{
    public function __construct(private array $map = []) {}

    public function get(string $type): string
    {
        if (! array_key_exists($type, $this->map)) {
            throw new NotFoundHttpException(sprintf('Feedbackable type "%s" not found.', $type));
        }

        return $this->map[$type];
    }
}
