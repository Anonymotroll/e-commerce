<?php
require_once 'config/db.php';

// Configurações
$res = $pdo->query("SELECT chave, valor FROM config");
$config = [];
while ($row = $res->fetch()) {
    $config[$row['chave']] = $row['valor'];
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Minha Landing Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Barra de navegação -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm" style="backdrop-filter: blur(10px); background-color: rgba(33, 37, 41, 0.95) !important;">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="#">
                <span class="text-primary me-2">DEV T.I.</span> Consultoria
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link px-3 active" aria-current="page" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="#servicos">Serviços</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-outline-primary fw-bold px-4 rounded-pill" href="https://wa.me/<?php echo $config['whatsapp']; ?>" target="_blank">Fale Conosco</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Cabeçalho -->
    <header style="display: flex; align-items: center; max-height: 80vh; overflow: hidden;">
        <img src="<?php echo $config['banner_url']; ?>" alt="Banner" class="img-fluid w-100">
    </header>
    <!-- Sessão de serviços -->
    <section id="servicos" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Nossas Especialidades</h2>
                <p class="text-muted">Consultoria estratégica para otimizar seus processos</p>
            </div>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-2 g-4">
                <?php
                $stmt = $pdo->query("SELECT * FROM servicos WHERE ativo = 1");
                $servicos = $stmt->fetchAll();
                foreach ($servicos as $s): ?>
                    <div class="col">
                        <div class="card border-0 shadow-sm h-100">
                            <img src="<?php echo $s['foto_url']; ?>" class="card-img-top" alt="..." style="height: 180px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="fw-bold mb-1"><?php echo $s['titulo']; ?></h5>
                                <p class="text-primary small fw-semibold mb-2"><?php echo $s['subtitulo']; ?></p>
                                <p class="card-text text-muted small"><?php echo $s['descricao']; ?></p>
                                <div class="mt-auto pt-3">
                                    <span class="d-block mb-3 fw-bold text-dark">RS$<?php echo number_format($s['valor'], 2, ',', '.'); ?></span>
                                    <button class="btn btn-dark w-100 btn-sm">Contratar Agora</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <!-- Rodapé -->
    <footer class="bg-dark text-white py-4 mt-5" id="contato">
        <div class="container text-center">
            <p>@T.I. Consultoria 2026</p>
            <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-3">
                <small><a class="text-white text-decoration-none" href="mailto:<?php echo $config['email_contato']; ?>"target="_blank"><?php echo $config['email_contato']; ?></a></small>
                <small><a class="text-white text-decoration-none" href="tel:<?php echo $config['telefone']; ?>" target="_blank">Telefone: <?php echo $config['telefone']; ?></a></small>
                <small><a class="text-white text-decoration-none" href="https://wa.me/<?php echo $config['whatsapp']; ?>" target="_blank">WhatsApp: <?php echo $config['whatsapp']; ?></a></small>
            </div>
        </div>
    </footer>
</body>
</html>