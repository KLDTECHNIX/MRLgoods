<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../scripts/bootstrap.php';

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

require_once __DIR__ . '/../../vendor/autoload.php';

try {
    \Stripe\Stripe::setApiKey($secret);

    $prices = \Stripe\Price::all([
        'active' => true,
        'expand' => ['data.product'],
        'limit' => 100,
        'type' => 'one_time'
    ]);

    $products = [];
    foreach ($prices->data as $price) {
        if (!isset($price->product) || !is_object($price->product) || !($price->product->active ?? false)) {
            continue;
        }
        $product = $price->product;
        $products[] = [
            'price_id' => $price->id,
            'unit_amount' => $price->unit_amount,
            'currency' => $price->currency,
            'name' => $product->name,
            'description' => $product->description,
            'image' => ($product->images[0] ?? null),
        ];
    }

    echo json_encode(['products' => $products], JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch Stripe products.']);
}
