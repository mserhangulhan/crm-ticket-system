<?php

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/TicketService.php';

use Crm\Ticket\TicketService;

header('Content-Type: application/json; charset=UTF-8');

$service = new TicketService();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if ($uri === '/tickets' || $uri === '/') {
    if ($method === 'GET') {
        $status = $_GET['status'] ?? null;
        $tickets = $service->listTickets($status);
        echo json_encode(['status' => 'success', 'data' => $tickets], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($method === 'POST') {
        $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        try {
            $ticket = $service->createTicket($body);
            http_response_code(201);
            echo json_encode(['status' => 'success', 'data' => $ticket], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }
}

if (preg_match('#^/tickets/(\d+)$#', $uri, $matches)) {
    $id = (int)$matches[1];
    if ($method === 'GET') {
        $ticket = $service->getTicket($id);
        if (!$ticket) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Ticket not found']);
            exit;
        }
        echo json_encode(['status' => 'success', 'data' => $ticket], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

http_response_code(404);
echo json_encode(['status' => 'error', 'message' => 'Endpoint not found']);
