<?php

namespace App\Services;

use App\Models\IntegrationLog;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * @param array<string, mixed> $payload
     */
    public function notify(string|array $channel, array $payload): void
    {
        $channels = is_array($channel) ? $channel : [$channel];

        collect($channels)->each(function ($singleChannel) use ($payload) {
            IntegrationLog::create([
                'channel' => $singleChannel,
                'status' => 'queued',
                'payload' => $payload,
            ]);

            Log::info('Notification queued', [
                'channel' => $singleChannel,
                'payload' => $payload,
            ]);
        });
    }
}
