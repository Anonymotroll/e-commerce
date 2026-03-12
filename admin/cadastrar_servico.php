<?php
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['foto'])) {
    
    $titulo    = $_POST['titulo'];
    $subtitulo = $_POST['subtitulo'];
    $descricao = $_POST['descricao'];
    $valor     = $_POST['valor'];
    
    // Configurações do Upload
    $diretorio_destino = "assets/uploads/servicos/";
    $arquivo_temp = $_FILES['foto']['tmp_name'];
    $nome_original = $_FILES['foto']['name'];
    
    // Gerar nome único para evitar sobrescrever arquivos com o mesmo nome
    $extensao = pathinfo($nome_original, PATHINFO_EXTENSION);
    $novo_nome = uniqid() . "." . $extensao;
    $caminho_db = $diretorio_destino . $novo_nome;
    $caminho_final = "../" . $diretorio_destino . $novo_nome;

    // Validações de segurança
    $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
    
    if (in_array(strtolower($extensao), $extensoes_permitidas)) {
        
        if (move_uploaded_file($arquivo_temp, $caminho_final)) {
            
            $sql = "INSERT INTO servicos (titulo, subtitulo, descricao, valor, foto_url) 
                    VALUES (:titulo, :subtitulo, :desc, :valor, :foto)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':titulo'    => $titulo,
                ':subtitulo' => $subtitulo,
                ':desc'      => $descricao,
                ':valor'     => $valor,
                ':foto'      => $caminho_db
            ]);

            echo "<div class='alert alert-success'>Serviço e imagem cadastrados com sucesso!</div>";
        } else {
            echo "Erro ao mover o arquivo.";
        }
    } else {
        echo "Formato de arquivo não permitido. Use JPG, PNG ou WEBP.";
    }
    exit();
}