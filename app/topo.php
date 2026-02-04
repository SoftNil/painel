<!DOCTYPE html>
<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');
header('Content-Type: text/html; charset=UTF-8');
include './conexao/conecta.php';
include 'funcoes.php';

if (Logado() ==  true) {
 //   include 'verifica.php';
 //   exit;    
}

 $query_4 = "SELECT * FROM configuracoes_4";
    $sql_4 = mysqli_query($conecta, $query_4);
    while ($linha_4 = mysqli_fetch_assoc($sql_4)) {
        $id_smtp_4 = $linha_4['id_smtp_4'];  
        $titulo_4 = $linha_4['titulo_4'];  
        $logo_4 = $linha_4['logo_4'];
        $descricao_4 = $linha_4['descricao_4'];
    }
    
    
?>
<html lang="pt-br">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="<?php echo $descricao_4; ?>">
        <meta name="author" content="">
        <title><?php echo $titulo_4; ?></title>
        <!--CSS -->
        <link href="<?php echo $dominio ?>/app/plugins/bootstrap5/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="<?php echo $dominio ?>/app/plugins/remixicon/remixicon.css" rel="stylesheet">
        <link rel="icon" href="<?php echo imgExiste($dominio, '/app/imagens/uploads/', $logo_4) ?>"/>
        
        <!-- Custom styles for this template -->
        <link href="style.css" rel="stylesheet">
    </head>
    <body>
      