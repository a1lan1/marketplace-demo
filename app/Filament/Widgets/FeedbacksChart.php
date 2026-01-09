<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Contracts\Repositories\FeedbackRepositoryInterface;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Override;

class FeedbacksChart extends ChartWidget
{
    protected ?string $heading = 'Review Sentiments (Geo Collector)';

    #[Override]
    protected function getData(): array
    {
        /** @var FeedbackRepositoryInterface $feedbackRepository */
        $feedbackRepository = resolve(FeedbackRepositoryInterface::class);
        $data = $feedbackRepository->getSentimentCountsForUser(Auth::id());

        $positive = $data['positive'] ?? 0;
        $neutral = $data['neutral'] ?? 0;
        $negative = $data['negative'] ?? 0;

        return [
            'datasets' => [
                [
                    'label' => 'Reviews',
                    'data' => [$positive, $neutral, $negative],
                    'backgroundColor' => ['#4ade80', '#94a3b8', '#f87171'],
                ],
            ],
            'labels' => ['Positive', 'Neutral', 'Negative'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
