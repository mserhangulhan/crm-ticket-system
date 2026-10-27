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
}
