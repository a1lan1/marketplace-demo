<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\DTO\UserActivityData;
use App\Enums\UserActivityType;
use App\Kafka\Producers\UserActivityProducer;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Random\RandomException;

class UserActivitySeeder extends Seeder
{
    /**
     * @throws RandomException
     */
    public function run(): void
    {
        $eventsToCreate = 2000;

        for ($i = 0; $i < $eventsToCreate; $i++) {
            $this->publishEvent(
                Arr::random(
                    UserActivityType::cases()
                )
            );
        }
    }

    /**
     * @throws RandomException
     */
    private function publishEvent(UserActivityType $eventType): void
    {
        $pages = [
            '/',
            '/home',
            '/pricing',
            '/about',
            '/contact',
            '/products/1',
            '/products/2',
            '/blog',
            '/blog/laravel-vue',
            '/dashboard',
        ];

        $now = Date::now();
        $minutesAgo = random_int(0, 360); // within the last 6 hours
        $ts = $now->copy()->subMinutes($minutesAgo)->format('Y-m-d H:i:s');
        $url = Arr::random($pages);
        $userId = (bool) random_int(0, 1) ? random_int(1, 50) : null;

        $dto = new UserActivityData(
            user_id: $userId,
            event_type: $eventType,
            url: $url,
            ts: $ts,
            data: $this->buildProps($eventType, $url),
        );

        try {
            app(UserActivityProducer::class)->publish($dto);
        } catch (Exception $exception) {
            $this->command->warn('UserActivitySeeder: failed to publish event - '.$exception->getMessage());
        }
    }

    private function buildProps(UserActivityType $type, string $url): array
    {
        $base = ['page' => $url, 'ua' => 'seeder-bot'];

        return $base + match ($type) {
            UserActivityType::CLICK => [
                'element' => Arr::random(['button', 'link', 'icon']),
                'id' => 'el-'.random_int(1, 999),
                'text' => Arr::random(['Buy', 'Submit', 'Save', 'Open', 'Details']),
            ],
            UserActivityType::SIGN_IN, UserActivityType::SIGN_UP => [
                'method' => Arr::random(['password', 'google', 'github']),
            ],
            UserActivityType::ERROR => [
                'message' => 'Random error',
                'code' => Arr::random(['E_NETWORK', 'E_TIMEOUT', 'E_VALIDATION', 'E_UNKNOWN']),
            ],
            default => [],
        };
    }
}
