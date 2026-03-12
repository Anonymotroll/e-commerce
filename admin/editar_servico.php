<?php
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $titulo = $_POST['titulo'];
    $subtitulo = $_POST['subtitulo'];
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];

    // Se uma nova foto foi enviada
    if (!empty($_FILES['foto']['name'])) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $novo_nome = uniqid() . "." . $ext;
        $caminho = "../assets/uploads/servicos/" . $novo_nome;
        $caminho_db = "assets/uploads/servicos/" . $novo_nome;
        
        move_uploaded_file($_FILES['foto']['tmp_name'], $caminho);
        
        $sql = "UPDATE servicos SET titulo=?, subtitulo=?, descricao=?, valor=?, foto_url=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$titulo, $subtitulo, $descricao, $valor, $caminho_db, $id]);
    } else {
        // Atualiza apenas os textos
        $sql = "UPDATE servicos SET titulo=?, subtitulo=?, descricao=?, valor=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$titulo, $subtitulo, $descricao, $valor, $id]);
    }

    header("Location: index.php?msg=editado");
    exit;
}