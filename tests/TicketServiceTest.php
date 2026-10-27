<?php

namespace Crm\Ticket\Tests;

use PHPUnit\Framework\TestCase;
use Crm\Ticket\TicketService;
use Crm\Ticket\Database;

class TicketServiceTest extends TestCase
{
    private TicketService $service;

    protected function setUp(): void
    {
        Database::reset();
        $this->service = new TicketService();
    }

    public function testCreateAndGetTicket(): void
    {
        $ticket = $this->service->createTicket([
            'title' => 'Örnek CRM Destek Talebi',
            'description' => 'Müşteri paneline giriş yapılamıyor.',
            'priority' => 'high',
            'customer_email' => 'destek@egeleon.com'
        ]);

        $this->assertNotNull($ticket);
        $this->assertEquals('Örnek CRM Destek Talebi', $ticket['title']);
        $this->assertEquals('open', $ticket['status']);
        $this->assertEquals('high', $ticket['priority']);

        $retrieved = $this->service->getTicket((int)$ticket['id']);
        $this->assertEquals($ticket['id'], $retrieved['id']);
    }

    public function testUpdateStatus(): void
    {
        $ticket = $this->service->createTicket([
            'title' => 'Durum Güncelleme Testi',
            'priority' => 'low'
        ]);

        $updated = $this->service->updateStatus((int)$ticket['id'], 'resolved');
        $this->assertTrue($updated);

        $refreshed = $this->service->getTicket((int)$ticket['id']);
        $this->assertEquals('resolved', $refreshed['status']);
    }

    public function testCreateTicketWithoutTitleThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->createTicket([]);
    }

    public function testInvalidPriorityThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->createTicket([
            'title' => 'Geçersiz Öncelik',
            'priority' => 'invalid-priority'
        ]);
    }

    public function testInvalidStatusUpdateThrowsException(): void
    {
        $ticket = $this->service->createTicket(['title' => 'Test']);
        $this->expectException(\InvalidArgumentException::class);
        $this->service->updateStatus((int)$ticket['id'], 'invalid_status');
    }

    public function testGetNonExistentTicketReturnsNull(): void
    {
        $ticket = $this->service->getTicket(99999);
        $this->assertNull($ticket);
    }

    public function testListTicketsWithFilter(): void
    {
        $ticket1 = $this->service->createTicket(['title' => 'Ticket 1']);
        $ticket2 = $this->service->createTicket(['title' => 'Ticket 2']);
        $this->service->updateStatus((int)$ticket2['id'], 'closed');

        $openTickets = $this->service->listTickets('open');
        $this->assertCount(1, $openTickets);
        $this->assertEquals('Ticket 1', $openTickets[0]['title']);

        $closedTickets = $this->service->listTickets('closed');
        $this->assertCount(1, $closedTickets);
        $this->assertEquals('Ticket 2', $closedTickets[0]['title']);
    }
}
