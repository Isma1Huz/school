<?php

declare(strict_types=1);

namespace App\Modules\Core\Notifications\Channels;

use App\Modules\Core\Models\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class SmsChannelHandler
{
    public function handle(Notification $notification): void
    {
        $tenant = $notification->tenant;
        $provider = $tenant?->settings['sms_provider'] ?? config('services.sms.default_provider', 'africas_talking');

        $recipients = $notification->data['recipients'] ?? [];
        $message = $notification->data['body'] ?? '';

        match ($provider) {
            'africas_talking' => $this->sendViaAfricasTalking($notification, $recipients, $message),
            'safaricom'       => $this->sendViaSafaricom($notification, $recipients, $message),
            default           => throw new \RuntimeException("Unsupported SMS provider: {$provider}"),
        };
    }

    private function sendViaAfricasTalking(Notification $notification, array $recipients, string $message): void
    {
        $apiKey   = config('services.africas_talking.api_key', '');
        $username = config('services.africas_talking.username', 'sandbox');
        $senderId = config('services.africas_talking.sender_id', '');

        try {
            // Stub: real HTTP call wrapped in try/catch
            $response = Http::withHeaders([
                'apiKey' => $apiKey,
                'Accept' => 'application/json',
            ])->post('https://api.africastalking.com/version1/messaging', [
                'username'  => $username,
                'to'        => implode(',', $recipients),
                'message'   => $message,
                'from'      => $senderId ?: null,
            ]);

            if (!$response->successful()) {
                throw new \RuntimeException('Africa\'s Talking API error: ' . $response->body());
            }

            $notification->update([
                'status'  => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('AfricasTalking SMS failed', [
                'notification_id' => $notification->id,
                'error'           => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function sendViaSafaricom(Notification $notification, array $recipients, string $message): void
    {
        $consumerKey    = config('services.safaricom.consumer_key', '');
        $consumerSecret = config('services.safaricom.consumer_secret', '');
        $shortCode      = config('services.safaricom.short_code', '');

        try {
            // Obtain Daraja access token
            $tokenResponse = Http::withBasicAuth($consumerKey, $consumerSecret)
                ->get('https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');

            if (!$tokenResponse->successful()) {
                throw new \RuntimeException('Safaricom token error: ' . $tokenResponse->body());
            }

            $accessToken = $tokenResponse->json('access_token');

            // Stub: SMS via Daraja (uses SMS API endpoint when available)
            $response = Http::withToken($accessToken)
                ->post('https://sandbox.safaricom.co.ke/v1/sms/send', [
                    'shortCode'  => $shortCode,
                    'recipients' => $recipients,
                    'message'    => $message,
                ]);

            if (!$response->successful()) {
                throw new \RuntimeException('Safaricom SMS API error: ' . $response->body());
            }

            $notification->update([
                'status'  => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Safaricom SMS failed', [
                'notification_id' => $notification->id,
                'error'           => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
