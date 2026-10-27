<?php

namespace Crm\Ticket;

class WebhookDispatcher
{
    /** @var array<string, array<callable|string>> */
    private array $listeners = [];

    /** @var array<array{event: string, payload: array}> */
    private array $dispatchedEvents = [];

    /**
     * Olay dinleyicisi kaydeder (URL veya callable).
     */
    public function subscribe(string $event, callable|string $handler): void
    {
        $this->listeners[$event][] = $handler;
    }

    /**
     * Belirtilen olayı ve veriyi dinleyicilere iletir.
     */
    public function dispatch(string $event, array $payload): int
    {
        $this->dispatchedEvents[] = [
            'event' => $event,
            'payload' => $payload,
            'timestamp' => date('c')
        ];

        if (empty($this->listeners[$event])) {
            return 0;
        }

        $successCount = 0;
        foreach ($this->listeners[$event] as $handler) {
            if (is_callable($handler)) {
                $handler($event, $payload);
                $successCount++;
            } elseif (is_string($handler) && filter_var($handler, FILTER_VALIDATE_URL)) {
                if ($this->sendHttpRequest($handler, $event, $payload)) {
                    $successCount++;
                }
            }
        }

        return $successCount;
    }

    /**
     * Gönderilen olay geçmişini döndürür (test/denetim amaçlı).
     */
    public function getDispatchedEvents(): array
    {
        return $this->dispatchedEvents;
    }

    /**
     * HTTP POST isteği gönderir.
     */
    protected function sendHttpRequest(string $url, string $event, array $payload): bool
    {
        $data = json_encode([
            'event' => $event,
            'timestamp' => date('c'),
            'data' => $payload
        ]);

        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n" .
                            "User-Agent: Crm-Ticket-Webhook/1.1\r\n",
                'content' => $data,
                'timeout' => 5
            ]
        ];

        $context = stream_context_create($opts);
        $result = @file_get_contents($url, false, $context);

        return $result !== false;
    }
}
