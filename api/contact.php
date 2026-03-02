<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../scripts/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$name = trim((string)($_POST['name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));
$honeypot = trim((string)($_POST['company'] ?? ''));
$started = (int)($_POST['form_started'] ?? 0);

if ($honeypot !== '') {
    http_response_code(400);
    echo json_encode(['error' => 'Spam detected.']);
    exit;
}

if ($started < 1 || time() - $started < 3) {
    http_response_code(400);
    echo json_encode(['error' => 'Please take a moment before submitting.']);
    exit;
}

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Please provide valid name, email, and message.']);
    exit;
}

$logDir = __DIR__ . '/../data';
if (!is_dir($logDir)) {
    mkdir($logDir, 0750, true);
}

$entry = sprintf("[%s] Name: %s | Email: %s\n%s\n---\n", date('c'), $name, $email, $message);
$ok = file_put_contents($logDir . '/contact-submissions.log', $entry, FILE_APPEND | LOCK_EX);

if ($ok === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to save your message right now.']);
    exit;
}

echo json_encode(['message' => 'Thanks! Your message has been received.']);
