<?php
include './conexao/conecta.php';

include 'funcoes.php';

$id  = (int)($_POST['id'] ?? 0);
$img = basename($_POST['img'] ?? '');

if (!$id || !$img) {
    exit('Dados inválidos');
}

$arquivo = __DIR__ . '/imagens/uploads/' . $img;

/* remove do banco */
mysqli_query($conecta, "DELETE FROM imagens_2 WHERE id_2 = $id");

/* remove o arquivo */
if (is_file($arquivo)) {
    unlink($arquivo);
}

echo 'Imagem excluída com sucesso';
