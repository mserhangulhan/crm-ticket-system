<?php

namespace Crm\Ticket;

use PDO;

class NotificationLog
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS notification_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                event TEXT NOT NULL,
                payload TEXT NOT NULL,
                status TEXT NOT NULL DEFAULT 'pending',
                attempts INTEGER NOT NULL DEFAULT 0,
                last_error TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                sent_at DATETIME
            )
        ");
    }

    public function log(string $event, array $payload, string $status = 'pending', int $attempts = 0, ?string $error = null): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO notification_logs (event, payload, status, attempts, last_error)
            VALUES (:event, :payload, :status, :attempts, :error)
        ");
        $stmt->execute([
            ':event' => $event,
            ':payload' => json_encode($payload),
            ':status' => $status,
            ':attempts' => $attempts,
            ':error' => $error
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function markSent(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE notification_logs 
            SET status = 'sent', sent_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");
        return $stmt->execute([':id' => $id]);
    }

    public function markFailed(int $id, string $error): bool
    {
        $stmt = $this->db->prepare("
            UPDATE notification_logs 
            SET status = 'failed', attempts = attempts + 1, last_error = :error 
            WHERE id = :id
        ");
        return $stmt->execute([':id' => $id, ':error' => $error]);
    }

    public function getLogs(?string $event = null, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT * FROM notification_logs";
        $params = [];
        
        if ($event !== null) {
            $sql .= " WHERE event = :event";
            $params[':event'] = $event;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $logs = $stmt->fetchAll();
        
        foreach ($logs as &$log) {
            $log['payload'] = json_decode($log['payload'], true);
        }
        
        return $logs;
    }

    public function getStats(): array
    {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
            FROM notification_logs
        ");
        return $stmt->fetch();
    }
}