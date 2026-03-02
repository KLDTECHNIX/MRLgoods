<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../scripts/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$secret = getenv('STRIPE_SECRET_KEY') ?: '';
if ($secret === '') {
    http_response_code(500);
    echo json_encode(['error' => 'Server configuration error: missing STRIPE_SECRET_KEY']);
    exit;
}

if (!file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    http_response_code(500);
    echo json_encode(['error' => 'Stripe SDK missing. Run composer install.']);
    exit;
}

$raw = file_get_contents('php://input') ?: '';
$input = json_decode($raw, true);
$priceId = $input['price_id'] ?? '';
$quantity = (int)($input['quantity'] ?? 1);

if (!preg_match('/^price_[A-Za-z0-9]+$/', $priceId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid price_id']);
    exit;
}

if ($quantity < 1 || $quantity > 10) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid quantity']);
    exit;
}

require_once __DIR__ . '/../../vendor/autoload.php';

$baseUrl = getenv('SITE_URL') ?: 'http://localhost:8080';

try {
    \Stripe\Stripe::setApiKey($secret);
    $session = \Stripe\Checkout\Session::create([
        'mode' => 'payment',
        'line_items' => [[
            'price' => $priceId,
            'quantity' => $quantity,
        ]],
        'success_url' => $baseUrl . '/products/?checkout=success',
        'cancel_url' => $baseUrl . '/products/?checkout=cancelled',
        'billing_address_collection' => 'auto',
    ]);

    echo json_encode(['url' => $session->url], JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create checkout session.']);
}
