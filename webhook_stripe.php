<?php
require 'vendor/autoload.php';
require 'config/db.php';
require 'config/env.php';

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
} catch(Exception $e) {
    http_response_code(400); exit();
}

if ($event->type == 'checkout.session.completed') {
    $session = $event->data->object;

    $venda_id = $session->client_reference_id;

    if ($venda_id) {
        $stmt = $pdo->prepare("UPDATE vendas SET status = 'pago' WHERE id = ?");
        $stmt->execute([$venda_id]);
    }
}

http_response_code(200);