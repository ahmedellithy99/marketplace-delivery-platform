<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WaClientService
{
    protected string $accessToken;

    protected string $instanceId;

    protected string $apiUrl = 'https://waclient.com/api/send';

    public function __construct()
    {
        $this->accessToken = config('services.waclient.access_token', '');
        $this->instanceId = config('services.waclient.instance_id', '');
    }

    public function sendText(string $phone, string $message): bool
    {
        if (empty($this->accessToken) || empty($this->instanceId)) {
            Log::warning('WaClient not configured — skipping WhatsApp message', [
                'phone' => $phone,
            ]);

            return false;
        }

        $phone = ltrim($phone, '+');

        $response = Http::post($this->apiUrl, [
            'number' => $phone,
            'type' => 'text',
            'message' => $message,
            'instance_id' => $this->instanceId,
            'access_token' => $this->accessToken,
        ]);

        if ($response->failed()) {
            Log::error('WaClient send failed', [
                'phone' => $phone,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }

        $body = $response->json();

        if (($body['status'] ?? '') !== 'success') {
            Log::warning('WaClient send returned non-success', [
                'phone' => $phone,
                'response' => $body,
            ]);

            return false;
        }

        return true;
    }
}
