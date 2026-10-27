<?php

namespace Crm\Ticket;

use PDO;
use InvalidArgumentException;

class TicketService
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    public function createTicket(array $data): array
    {
        if (empty($data['title'])) {
            throw new InvalidArgumentException('Ticket title is required.');
        }

        $priority = $data['priority'] ?? 'medium';
        $validPriorities = ['low', 'medium', 'high', 'urgent'];
        if (!in_array($priority, $validPriorities, true)) {
            throw new InvalidArgumentException("Invalid priority: $priority");
        }

        $stmt = $this->db->prepare("
            INSERT INTO tickets (title, description, priority, status, customer_email)
            VALUES (:title, :description, :priority, 'open', :customer_email)
        ");

        $stmt->execute([
            ':title' => $data['title'],
            ':description' => $data['description'] ?? '',
            ':priority' => $priority,
            ':customer_email' => $data['customer_email'] ?? null,
        ]);

        $id = (int)$this->db->lastInsertId();
        return $this->getTicket($id);
    }

    public function getTicket(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM tickets WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $ticket = $stmt->fetch();

        return $ticket ?: null;
    }

    public function listTickets(?string $status = null): array
    {
        if ($status !== null) {
            $stmt = $this->db->prepare("SELECT * FROM tickets WHERE status = :status ORDER BY id DESC");
            $stmt->execute([':status' => $status]);
        } else {
            $stmt = $this->db->query("SELECT * FROM tickets ORDER BY id DESC");
        }

        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status): bool
    {
        $validStatuses = ['open', 'in_progress', 'resolved', 'closed'];
        if (!in_array($status, $validStatuses, true)) {
            throw new InvalidArgumentException("Invalid status: $status");
        }

        $stmt = $this->db->prepare("
            UPDATE tickets 
            SET status = :status, updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");

        return $stmt->execute([
            ':status' => $status,
            ':id' => $id,
        ]);
    }
}
