<?php
require 'vendor/autoload.php';
require 'config/db.php';
require 'config/env.php';

\Stripe\Stripe::setApiKey($stripe_secret_key);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $servico_id = $_POST['servico_id'];

    $stmt = $pdo->prepare("SELECT * FROM servicos WHERE id = ?");
    $stmt->execute([$servico_id]);
    $servico = $stmt->fetch();

    if ($servico) {
        try {
            $sql_venda = "INSERT INTO vendas (servico_id, valor_pago, status) VALUES (?, ?, 'pendente')";
            $stmt_venda = $pdo->prepare($sql_venda);
            $stmt_venda->execute([$servico_id, $servico['valor']]);
            $venda_id = $pdo->lastInsertId();

            // Configura a Sessão do Stripe
            $checkout_session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'client_reference_id' => $venda_id,
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'brl',
                        'unit_amount' => $servico['valor'] * 100,
                        'product_data' => [
                            'name' => $servico['titulo'],
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => 'http://localhost/e-commerce/compra-efetuada.php?venda_id=' . $venda_id,
                'cancel_url' => 'http://localhost/e-commerce/index.php',
            ]);

            // Atualizar o registro com o ID da sessão do Stripe
            $update = $pdo->prepare("UPDATE vendas SET stripe_checkout_id = ? WHERE id = ?");
            $update->execute([$checkout_session->id, $venda_id]);

            header("Location: " . $checkout_session->url);
            exit;

        } catch (Exception $e) {
            die("Erro ao processar checkout: " . $e->getMessage());
        }
    }
}