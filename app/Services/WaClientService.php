<?php

namespace App\Services;

use App\Events\WhatsAppMessageFailed;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WaClientService
{
    protected string $accessToken;

    protected string $instanceId;

    protected string $apiUrl = 'https://waclient.com/api/send';

    protected int $timeout;

    protected bool $logSuccess;

    protected array $retryDelays = [1000, 5000, 10000];

    public function __construct()
    {
        $this->accessToken = config('services.waclient.access_token', '');
        $this->instanceId = config('services.waclient.instance_id', '');
        $this->timeout = config('services.waclient.timeout', 30);
        $this->logSuccess = config('services.waclient.log_success', true);
    }

    public function sendText(string $phone, string $message): bool
    {
        $traceId = Str::uuid()->toString();

        if (empty($this->accessToken) || empty($this->instanceId)) {
            $this->logWarning('WaClient not configured — skipping WhatsApp message', $phone, $traceId);

            return false;
        }

        if (empty(trim($message))) {
            $this->logWarning('WaClient empty message — skipping WhatsApp message', $phone, $traceId);

            return false;
        }

        $phone = ltrim($phone, '+');

        if (!$this->isValidPhone($phone)) {
            $this->logWarning('WaClient invalid phone format — skipping WhatsApp message', $phone, $traceId);

            return false;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->retry(
                    3,
                    function (int $attempt, Exception $exception) use ($traceId, $phone) {
                        $this->logRetry($traceId, $phone, $exception);

                        if ($this->isRateLimit($exception)) {
                            $retryAfter = $this->getRetryAfter($exception);

                            return $retryAfter > 0 ? $retryAfter * 1000 : 5000;
                        }

                        return $this->retryDelays[$attempt - 1] ?? 10000;
                    },
                    null,
                    true
                )
                ->post($this->apiUrl, [
                    'number' => $phone,
                    'type' => 'text',
                    'message' => $message,
                    'instance_id' => $this->instanceId,
                    'access_token' => $this->accessToken,
                ]);
        } catch (Exception $e) {
            $this->logError('WaClient HTTP exception after retries', $phone, $traceId, $e);
            $this->deadLetter($phone, $message, 'http_exception_after_retries: '.$e->getMessage(), $traceId);

            return false;
        }

        $body = $response->json();

        if (($body['status'] ?? '') !== 'success') {
            $this->logWarning('WaClient send returned non-success', $phone, $traceId, $body);
            $this->deadLetter($phone, $message, 'non_success_response: '.json_encode($body), $traceId);

            return false;
        }

        if ($this->logSuccess) {
            $this->logInfo('WaClient message sent', $phone, $traceId);
        }

        return true;
    }

    protected function isValidPhone(string $phone): bool
    {
        return preg_match('/^\d{10,15}$/', $phone) === 1;
    }

    protected function maskPhone(string $phone): string
    {
        $length = strlen($phone);

        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return str_repeat('*', $length - 4).substr($phone, -4);
    }

    protected function isRateLimit(Exception $exception): bool
    {
        return $exception instanceof RequestException && $exception->response?->status() === 429;
    }

    protected function getRetryAfter(Exception $exception): int
    {
        if (!$exception instanceof RequestException) {
            return 0;
        }

        $retryAfter = (int) $exception->response->header('Retry-After');

        return $retryAfter > 0 ? min($retryAfter, 60) : 0;
    }

    protected function deadLetter(string $phone, string $message, string $reason, string $traceId): void
    {
        WhatsAppMessageFailed::dispatch($phone, $message, $reason, $traceId);
    }

    protected function logInfo(string $messageText, string $phone, string $traceId): void
    {
        if (app()->environment('testing')) {
            return;
        }

        Log::info($messageText, [
            'trace_id' => $traceId,
            'phone' => $this->maskPhone($phone),
        ]);
    }

    protected function logWarning(string $messageText, string $phone, string $traceId, ?array $context = null): void
    {
        if (app()->environment('testing')) {
            return;
        }

        Log::warning($messageText, array_filter([
            'trace_id' => $traceId,
            'phone' => $this->maskPhone($phone),
            'context' => $context,
        ]));
    }

    protected function logError(
        string $messageText,
        string $phone,
        string $traceId,
        ?Exception $exception = null,
        ?int $status = null,
        ?string $body = null,
    ): void {
        if (app()->environment('testing')) {
            return;
        }

        Log::error($messageText, array_filter([
            'trace_id' => $traceId,
            'phone' => $this->maskPhone($phone),
            'error' => $exception?->getMessage(),
            'status' => $status,
            'body' => $body,
        ]));
    }

    protected function logRetry(string $traceId, string $phone, Exception $exception): void
    {
        if (app()->environment('testing')) {
            return;
        }

        Log::warning('WaClient retry attempt', [
            'trace_id' => $traceId,
            'phone' => $this->maskPhone($phone),
            'error' => $exception->getMessage(),
        ]);
    }
}
