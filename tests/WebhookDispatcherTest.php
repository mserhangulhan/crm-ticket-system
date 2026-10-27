<?php

namespace Crm\Ticket\Tests;

use PHPUnit\Framework\TestCase;
use Crm\Ticket\WebhookDispatcher;
use Crm\Ticket\TicketService;
use Crm\Ticket\Database;

class WebhookDispatcherTest extends TestCase
{
    private WebhookDispatcher $dispatcher;

    protected function setUp(): void
    {
        Database::reset();
        $this->dispatcher = new WebhookDispatcher();
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
}
