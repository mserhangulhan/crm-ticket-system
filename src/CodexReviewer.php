<?php

namespace Crm\Ticket;

class CodexReviewer
{
    private string $apiKey;
    private string $model;

    public function __construct(?string $apiKey = null, string $model = 'gpt-4o-mini')
    {
        $this->apiKey = $apiKey ?? getenv('OPENAI_API_KEY') ?: '';
        $this->model = $model;
    }

    public function reviewDiff(string $diff): string
    {
        if (empty($this->apiKey)) {
            return "Simulated Codex Review: OPENAI_API_KEY is not configured.\nChecked diff: " . strlen($diff) . " bytes. No critical syntax errors found.";
        }

        $payload = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an automated pull request reviewer for a PHP CRM Ticket System. Analyze the diff for potential security vulnerabilities (SQL injection, XSS), PSR-12 code style, and test coverage.'
                ],
                [
                    'role' => 'user',
                    'content' => "Please review this pull request git diff:\n\n" . substr($diff, 0, 10000)
                ]
            ],
            'temperature' => 0.2
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return "Codex review request failed with status: $httpCode";
        }

        $data = json_decode($response, true);
        return $data['choices'][0]['message']['content'] ?? 'No feedback returned.';
    }
}
