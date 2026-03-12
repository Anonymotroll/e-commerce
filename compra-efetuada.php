<?php
require_once 'config/db.php';

$venda_id = isset($_GET['venda_id']) ? $_GET['venda_id'] : null;

if ($venda_id) {
    $stmt = $pdo->prepare("SELECT v.*, s.titulo FROM vendas v JOIN servicos s ON v.servico_id = s.id WHERE v.id = ?");
    $stmt->execute([$venda_id]);
    $venda = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Pagamento Confirmado - T.I. Consultoria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <div class="container vh-100 d-flex align-items-center justify-content-center">
        <div class="card shadow border-0 text-center p-5" style="max-width: 500px;">
            <div class="mb-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
            </div>
            
            <h1 class="fw-bold">Pagamento Confirmado!</h1>
            <p class="text-muted">Olá! Recebemos a confirmação da sua contratação para o serviço: <br>
                <strong class="text-dark"><?php echo $venda ? $venda['titulo'] : 'Consultoria de TI'; ?></strong>
            </p>

            <div class="bg-light p-3 rounded mb-4 border">
                <small class="text-muted d-block">Protocolo do Pedido:</small>
                <span class="fw-bold">#<?php echo str_pad($venda_id, 5, '0', STR_PAD_LEFT); ?></span>
            </div>

            <p class="small text-secondary mb-4">
                Um e-mail de confirmação foi enviado. Nossa equipe técnica entrará em contato em até 24 horas úteis para iniciar o cronograma de implantação.
            </p>

            <div class="d-grid gap-2">
                <a href="index.php" class="btn btn-dark btn-lg">Voltar para a Home</a>
                <button onclick="window.print()" class="btn btn-outline-secondary">Imprimir Comprovante</button>
            </div>
        </div>
    </div>

</body>
</html>