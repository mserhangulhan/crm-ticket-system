<?php

namespace Crm\Ticket\Tests;

use PHPUnit\Framework\TestCase;
use Crm\Ticket\WebhookDispatcher;
use Crm\Ticket\NotificationLog;
use Crm\Ticket\TicketService;
use Crm\Ticket\Database;

class WebhookDispatcherTest extends TestCase
{
    private WebhookDispatcher $dispatcher;
    private NotificationLog $notificationLog;

    protected function setUp(): void
    {
        Database::reset();
        $this->notificationLog = new NotificationLog();
        $this->dispatcher = new WebhookDispatcher($this->notificationLog);
    }

    public function testSubscribeAndDispatchCallable(): void
    {
        $received = [];
        $this->dispatcher->subscribe('ticket.created', function (string $event, array $payload) use (&$received) {
            $received[] = [
                'event' => $event,
                'payload' => $payload,
            ];
        });

        $count = $this->dispatcher->dispatch('ticket.created', ['id' => 1, 'title' => 'Örnek']);

        $this->assertEquals(1, $count);
        $this->assertCount(1, $received);
        $this->assertEquals('ticket.created', $received[0]['event']);
        $this->assertEquals(1, $received[0]['payload']['id']);
    }

    public function testTicketServiceTriggersWebhookOnCreate(): void
    {
        $service = new TicketService(null, $this->dispatcher);

        $eventsTriggered = [];
        $this->dispatcher->subscribe('ticket.created', function (string $event, array $payload) use (&$eventsTriggered) {
            $eventsTriggered[] = $payload;
        });

        $ticket = $service->createTicket([
            'title' => 'Webhook Bilet Testi',
            'priority' => 'high'
        ]);

        $this->assertCount(1, $eventsTriggered);
        $this->assertEquals($ticket['id'], $eventsTriggered[0]['id']);
        $this->assertEquals('Webhook Bilet Testi', $eventsTriggered[0]['title']);
    }

    public function testTicketServiceTriggersWebhookOnStatusUpdate(): void
    {
        $service = new TicketService(null, $this->dispatcher);

        $statusUpdates = [];
        $this->dispatcher->subscribe('ticket.status_updated', function (string $event, array $payload) use (&$statusUpdates) {
            $statusUpdates[] = $payload;
        });

        $ticket = $service->createTicket(['title' => 'Durum Testi']);
        $service->updateStatus((int)$ticket['id'], 'resolved');

        $this->assertCount(1, $statusUpdates);
        $this->assertEquals('resolved', $statusUpdates[0]['new_status']);
        $this->assertEquals($ticket['id'], $statusUpdates[0]['ticket']['id']);
    }

    public function testDispatchWithoutListenersReturnsZero(): void
    {
        $count = $this->dispatcher->dispatch('unhandled.event', ['key' => 'val']);
        $this->assertEquals(0, $count);
        $this->assertCount(1, $this->dispatcher->getDispatchedEvents());
    }

    public function testDispatchLogsToNotificationLog(): void
    {
        $this->dispatcher->subscribe('test.logged.event', function () {});
        $this->dispatcher->dispatch('test.logged.event', ['data' => 'logged']);

        $logs = $this->notificationLog->getLogs('test.logged.event');
        $this->assertCount(1, $logs);
        $this->assertEquals('sent', $logs[0]['status']);
        $this->assertEquals(['data' => 'logged'], $logs[0]['payload']);
    }

    public function testDispatchWithFailedUrlLogsFailure(): void
    {
        $this->dispatcher->subscribe('test.failed.url', 'https://invalid.invalid.url/test');
        $this->dispatcher->dispatch('test.failed.url', ['data' => 'failed']);

        $logs = $this->notificationLog->getLogs('test.failed.url');
        $this->assertCount(1, $logs);
        $this->assertEquals('failed', $logs[0]['status']);
        $this->assertEquals(1, $logs[0]['attempts']);
    }

    public function testCallableListenerMarksSent(): void
    {
        $this->dispatcher->subscribe('test.callable', function () {});
        $this->dispatcher->dispatch('test.callable', ['data' => 'callable']);

        $logs = $this->notificationLog->getLogs('test.callable');
        $this->assertEquals('sent', $logs[0]['status']);
    }
}
