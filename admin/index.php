<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: login.php");
    exit;
}

require '../config/db.php';

// Lógica de Exclusão
if (isset($_GET['excluir'])) {
    $id = $_GET['excluir'];
    
    $stmt = $pdo->prepare("SELECT * FROM servicos WHERE id = ?");
    $stmt->execute([$id]);
    $servico = $stmt->fetch();
    
    // Remove a foto da pasta
    if ($servico) {
        unlink("../" . $servico['foto_url']);
    }

    $stmt = $pdo->prepare("DELETE FROM servicos WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: index.php?msg=sucesso");
    exit;
}

// Busca todos os serviços e configuração
$servicos = $pdo->query("SELECT * FROM servicos ORDER BY id DESC")->fetchAll();
$res = $pdo->query("SELECT chave, valor FROM config");
$config = [];
while ($row = $res->fetch()) {
    $config[$row['chave']] = $row['valor'];
}

// Consulta para os Cards de Resumo
$totalVendas = $pdo->query("SELECT COUNT(*) FROM vendas WHERE status = 'pago'")->fetchColumn();
$faturamento = $pdo->query("SELECT SUM(valor_pago) FROM vendas WHERE status = 'pago'")->fetchColumn();
$vendasPendentes = $pdo->query("SELECT COUNT(*) FROM vendas WHERE status = 'pendente'")->fetchColumn();

// Consulta para a Tabela de Vendas
$sqlVendas = "SELECT v.*, s.titulo as servico_nome 
              FROM vendas v 
              LEFT JOIN servicos s ON v.servico_id = s.id 
              ORDER BY v.data_venda DESC";
$listaVendas = $pdo->query($sqlVendas)->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Admin - Consultoria TI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <div>
                <h2 class="h4 mb-0 text-dark">Painel Administrativo</h2>
                <small class="text-muted">Bem-vindo, Gestor de TI</small>
            </div>
            <div class="d-flex gap-2">
                <a href="../index.php" class="btn btn-outline-secondary btn-sm" target="_blank">
                    <i class="bi bi-eye"></i> Ver Site
                </a>
                <a href="logout.php" class="btn btn-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Sair
                </a>
            </div>
        </div>
    </div>
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
        <div>
            <h2 class="h4 mb-0">Gerenciar Serviços</h2>
            <small class="text-muted">Controle seu catálogo de TI</small>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovoServico">
            <i class="bi bi-plus-lg"></i> Novo Serviço
        </button>
        <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#modalConfig">
            <i class="bi bi-gear"></i> Ajustes do Site
        </button>

        <div class="modal fade" id="modalConfig" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Configurações Gerais</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="editar_config.php" method="POST" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Novo Banner (Substituir)</label>
                                <input type="file" name="banner" class="form-control" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">E-mail</label>
                                <input type="email" name="email_contato" class="form-control" value="<?php echo $config['email_contato']; ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Telefone</label>
                                <input type="text" name="telefone" class="form-control" value="<?php echo $config['telefone']; ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">WhatsApp (Só números)</label>
                                <input type="text" name="whatsapp" class="form-control" value="<?php echo $config['whatsapp']; ?>">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary w-100">Atualizar Site</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Operação realizada com sucesso!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Miniatura</th>
                        <th>Título</th>
                        <th>Subtítulo</th>
                        <th>Valor</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($servicos as $s): ?>
                    <tr>
                        <td>
                            <img src="../<?php echo $s['foto_url']; ?>" class="rounded" style="width: 60px; height: 45px; object-fit: cover;">
                        </td>
                        <td class="fw-bold"><?php echo $s['titulo']; ?></td>
                        <td class="text-muted small"><?php echo $s['subtitulo']; ?></td>
                        <td>R$ <?php echo number_format($s['valor'], 2, ',', '.'); ?></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-warning me-2 btn-editar" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalEditarServico"
                                    data-id="<?php echo $s['id']; ?>"
                                    data-titulo="<?php echo $s['titulo']; ?>"
                                    data-subtitulo="<?php echo $s['subtitulo']; ?>"
                                    data-descricao="<?php echo $s['descricao']; ?>"
                                    data-valor="<?php echo $s['valor']; ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="index.php?excluir=<?php echo $s['id']; ?>" 
                               class="btn btn-sm btn-outline-danger" 
                               onclick="return confirm('Tem certeza que deseja excluir?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="container my-5">
    
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-uppercase small fw-bold opacity-75">Faturamento Total</h6>
                        <h3 class="mb-0 fw-bold">R$ <?php echo number_format($faturamento ?? 0, 2, ',', '.'); ?></h3>
                    </div>
                    <i class="bi bi-currency-dollar fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-uppercase small fw-bold opacity-75">Vendas Concluídas</h6>
                        <h3 class="mb-0 fw-bold"><?php echo $totalVendas; ?></h3>
                    </div>
                    <i class="bi bi-cart-check fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning text-dark p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-uppercase small fw-bold opacity-75">Aguardando Pagamento</h6>
                        <h3 class="mb-0 fw-bold"><?php echo $vendasPendentes; ?></h3>
                    </div>
                    <i class="bi bi-clock-history fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-list-stars me-2 text-primary"></i>Histórico de Contratações</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID Pedido</th>
                        <th>Data</th>
                        <th>Serviço</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Stripe ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listaVendas)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">Nenhuma venda registrada ainda.</td></tr>
                    <?php else: ?>
                        <?php foreach ($listaVendas as $v): ?>
                        <tr>
                            <td><span class="badge bg-light text-dark border">#<?php echo str_pad($v['id'], 5, '0', STR_PAD_LEFT); ?></span></td>
                            <td class="small text-muted"><?php echo date('d/m/Y H:i', strtotime($v['data_venda'])); ?></td>
                            <td class="fw-bold"><?php echo $v['servico_nome'] ?? 'Serviço Removido'; ?></td>
                            <td>R$ <?php echo number_format($v['valor_pago'], 2, ',', '.'); ?></td>
                            <td>
                                <?php if($v['status'] == 'pago'): ?>
                                    <span class="badge rounded-pill bg-success-subtle text-success border border-success px-3">Pago</span>
                                <?php elseif($v['status'] == 'pendente'): ?>
                                    <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning px-3">Pendente</span>
                                <?php else: ?>
                                    <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger px-3">Cancelado</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-secondary font-monospace"><?php echo $v['stripe_checkout_id'] ?: '---'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="mt-4 text-center">
    <a href="../index.php" class="btn btn-link text-secondary" target="_blank">Voltar para o site</a>
</div>

<div class="modal fade" id="modalNovoServico" tabindex="-1" aria-labelledby="modalNovoServicoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg"> <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalNovoServicoLabel">Cadastrar Novo Serviço</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="cadastrar_servico.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Título do Serviço</label>
                            <input type="text" name="titulo" class="form-control" placeholder="Ex: Cloud Computing" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Subtítulo</label>
                            <input type="text" name="subtitulo" class="form-control" placeholder="Ex: Infraestrutura">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descrição</label>
                        <textarea name="descricao" class="form-control" rows="3" placeholder="Detalhes do que é oferecido..." required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Valor</label>
                            <input type="text" name="valor_display" class="form-control" 
                                id="display-valor-id" data-target="real-valor-id" placeholder="R$ 0,00">
                            
                            <input type="hidden" name="valor" id="real-valor-id">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Imagem do Card</label>
                            <input type="file" name="foto" class="form-control" accept="image/*" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4">Salvar Serviço</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarServico" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold">Editar Serviço</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="editar_servico.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit-id">
                
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Título</label>
                            <input type="text" name="titulo" id="edit-titulo" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Subtítulo</label>
                            <input type="text" name="subtitulo" id="edit-subtitulo" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descrição</label>
                        <textarea name="descricao" id="edit-descricao" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Valor</label>
                            <input type="text" name="valor_display" class="form-control" 
                                id="edit-display-valor" data-target="edit-real-valor" placeholder="R$ 0,00">
                            
                            <input type="hidden" name="valor" id="edit-real-valor">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nova Imagem (opcional)</label>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                            <small class="text-muted">Deixe em branco para manter a atual.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning px-4 fw-bold">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>
        
    <script>
    document.querySelectorAll('.btn-editar').forEach(button => {
        button.addEventListener('click', function() {
            // Captura os dados do botão
            const id = this.getAttribute('data-id');
            const titulo = this.getAttribute('data-titulo');
            const subtitulo = this.getAttribute('data-subtitulo');
            const descricao = this.getAttribute('data-descricao');
            const valor = this.getAttribute('data-valor');

            // Preenche os campos do modal
            document.getElementById('edit-id').value = id;
            document.getElementById('edit-titulo').value = titulo;
            document.getElementById('edit-subtitulo').value = subtitulo;
            document.getElementById('edit-descricao').value = descricao;

            // Dentro do seu script anterior de abrir modal:
            const valorFormatado = parseFloat(valor).toLocaleString('pt-br', { style: 'currency', currency: 'BRL' });
            document.getElementById('edit-display-valor').value = valorFormatado;
            document.getElementById('edit-real-valor').value = valor; // Valor puro para o banco
        });
    });
    function formatarMoeda(input) {
        let valor = input.value;

        // Remove tudo o que não é dígito
        valor = valor.replace(/\D/g, "");

        // Converte para decimal (divide por 100)
        valor = (valor / 100).toFixed(2) + "";

        // Troca ponto por vírgula e adiciona separador de milhar
        valor = valor.replace(".", ",");
        valor = valor.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");

        input.value = "R$ " + valor;
    }

    // Aplica aos campos de valor no Modal de Cadastro e Edição
    document.querySelectorAll('input[name="valor_display"]').forEach(input => {
        input.addEventListener('input', function() {
            formatarMoeda(this);
            
            // Salva o valor puro (sem R$ e com ponto) no input real que vai para o PHP
            const valorPuro = this.value.replace("R$ ", "").replace(/\./g, "").replace(",", ".");
            const targetId = this.getAttribute('data-target');
            document.getElementById(targetId).value = valorPuro;
        });
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>