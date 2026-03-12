<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $campos = ['email_contato', 'telefone', 'whatsapp'];
    
    foreach ($campos as $chave) {
        if (isset($_POST[$chave])) {
            $stmt = $pdo->prepare("UPDATE config SET valor = ? WHERE chave = ?");
            $stmt->execute([$_POST[$chave], $chave]);
        }
    }

    if (!empty($_FILES['banner']['name'])) {
        $stmt_busca = $pdo->prepare("SELECT valor FROM config WHERE chave = 'banner_url'");
        $stmt_busca->execute();
        $banner_antigo = $stmt_busca->fetchColumn();

        $caminho_db = "assets/uploads/banner/" . uniqid() . "_" . $_FILES['banner']['name'];
        $caminho_novo = "../" . $caminho_db;

        if (move_uploaded_file($_FILES['banner']['tmp_name'], $caminho_novo)) {
            if ($banner_antigo) {
                $caminho_antigo_fisico = "../" . $banner_antigo;
                if (file_exists($caminho_antigo_fisico)) {
                    unlink($caminho_antigo_fisico);
                }
            }

            $stmt = $pdo->prepare("UPDATE config SET valor = ? WHERE chave = ?");
            $stmt->execute([$caminho_db, 'banner_url']);
        }
    }

    header("Location: index.php?msg=config_ok");
    exit;
}