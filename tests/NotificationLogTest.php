<?php

namespace Crm\Ticket\Tests;

use PHPUnit\Framework\TestCase;
use Crm\Ticket\NotificationLog;
use Crm\Ticket\Database;

class NotificationLogTest extends TestCase
{
    private NotificationLog $log;

    protected function setUp(): void
    {
        Database::reset();
        $this->log = new NotificationLog();
    }

    public function testLogCreatesEntry(): void
    {
        $id = $this->log->log('test.event', ['key' => 'value']);
        $this->assertGreaterThan(0, $id);

        $logs = $this->log->getLogs('test.event');
        $this->assertCount(1, $logs);
        $this->assertEquals('test.event', $logs[0]['event']);
        $this->assertEquals('pending', $logs[0]['status']);
        $this->assertEquals(['key' => 'value'], $logs[0]['payload']);
    }

    public function testMarkSentUpdatesStatus(): void
    {
        $id = $this->log->log('sent.event', ['data' => 'test']);
        $this->assertTrue($this->log->markSent($id));

        $logs = $this->log->getLogs('sent.event');
        $this->assertEquals('sent', $logs[0]['status']);
        $this->assertNotNull($logs[0]['sent_at']);
    }

    public function testMarkFailedIncrementsAttempts(): void
    {
        $id = $this->log->log('failed.event', ['data' => 'test']);
        $this->assertTrue($this->log->markFailed($id, 'Connection timeout'));

        $logs = $this->log->getLogs('failed.event');
        $this->assertEquals('failed', $logs[0]['status']);
        $this->assertEquals(1, $logs[0]['attempts']);
        $this->assertEquals('Connection timeout', $logs[0]['last_error']);
    }

    public function testGetLogsWithFilter(): void
    {
        $this->log->log('event.a', ['n' => 1]);
        $this->log->log('event.b', ['n' => 2]);
        $this->log->log('event.a', ['n' => 3]);

        $logsA = $this->log->getLogs('event.a');
        $this->assertCount(2, $logsA);

        $logsB = $this->log->getLogs('event.b');
        $this->assertCount(1, $logsB);

        $allLogs = $this->log->getLogs(null);
        $this->assertCount(3, $allLogs);
    }

    public function testGetStats(): void
    {
        $this->log->log('stats.a', []);
        $this->log->log('stats.b', []);
        $this->log->log('stats.c', []);
        $id = $this->log->log('stats.d', []);
        $this->log->markSent($id);
        $id2 = $this->log->log('stats.e', []);
        $this->log->markFailed($id2, 'Error');

        $stats = $this->log->getStats();
        $this->assertEquals(5, $stats['total']);
        $this->assertEquals(1, $stats['sent']);
        $this->assertEquals(1, $stats['failed']);
        $this->assertEquals(3, $stats['pending']);
    }
}