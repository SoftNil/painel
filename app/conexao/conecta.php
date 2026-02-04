<?php
$server = $_SERVER['SERVER_NAME'];
$endereco = $_SERVER['REQUEST_URI'];
// Inclui os detalhes da conexão
require 'detalhes_conexao.php';
// Tenta conectar e executar a configuracões
// Cria o objeto da conexão
$conecta = mysqli_connect($servidor, $usuario, $senha);
$seleciona = mysqli_select_db($conecta, $bd);
mysqli_set_charset($conecta, 'utf8');
?>
