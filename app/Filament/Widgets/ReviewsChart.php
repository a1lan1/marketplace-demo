<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Feedback;
use Filament\Widgets\ChartWidget;
use Override;

class ReviewsChart extends ChartWidget
{
    protected ?string $heading = 'Review Sentiments (Geo Collector)';

    #[Override]
    protected function getData(): array
    {
        $data = Feedback::query()
            ->selectRaw('count(*) as total, sentiment')
            ->groupBy('sentiment')
            ->pluck('total', 'sentiment')
            ->toArray();

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
