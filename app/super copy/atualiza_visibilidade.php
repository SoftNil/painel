<?php
include '../conexao/conecta.php';

// Recebendo dados via POST
 $tabela = isset($_POST['tabela']) ? $_POST['tabela'] : '';
 $coluna = isset($_POST['coluna']) ? $_POST['coluna'] : '';
 $visibleValue = isset($_POST['visible']) && $_POST['visible'] === 'true' ? 'true' : 'false';

 $resposta = array('status' => 'erro', 'mensagem' => 'Dados inválidos.');

if ($tabela && $coluna) {
    // 1. Obter informações completas da coluna atual para não perder definições (Type, Null, Default, etc)
    $queryColuna = mysqli_query($conecta, "SHOW FULL COLUMNS FROM `$tabela` WHERE Field = '$coluna'");
    
    if ($queryColuna && mysqli_num_rows($queryColuna) > 0) {
        $dadosColuna = mysqli_fetch_assoc($queryColuna);
        $comentarioAtual = $dadosColuna['Comment'];
        
        // 2. Atualizar a string do comentário
        // Substitui visible=true ou visible=false pelo novo valor
        $novoComentario = preg_replace('/\bvisible=(true|false)\b/i', 'visible=' . $visibleValue, $comentarioAtual);
        
        // Se por algum motivo o preg_replace falhar (ex: string muito estranha), mantemos o atual para não quebrar
        if ($novoComentario === null) {
            $novoComentario = $comentarioAtual;
        }

        // 3. Montar a definição da coluna para o ALTER TABLE
        // É necessário reconstruir a definição (tipo, nulo, extra, etc) pois o ALTER TABLE MODIFY exige isso
        $tipo = $dadosColuna['Type'];
        $nulo = ($dadosColuna['Null'] === 'NO') ? 'NOT NULL' : 'NULL';
        $chave = ($dadosColuna['Key'] === 'PRI') ? 'PRIMARY KEY' : ''; // Cuidado ao alterar PK
        $extra = $dadosColuna['Extra']; // Ex: auto_increment
        
        // Tratar valor padrão se existir
        $padrao = '';
        if ($dadosColuna['Default'] !== null) {
            $padrao = "DEFAULT '" . mysqli_real_escape_string($conecta, $dadosColuna['Default']) . "'";
        } elseif ($dadosColuna['Default'] === 'NULL' && $dadosColuna['Null'] === 'YES') {
            $padrao = "DEFAULT NULL";
        }

        // Escapar o comentário para evitar SQL Injection e quebras de string
        $comentarioEscapado = mysqli_real_escape_string($conecta, $novoComentario);

        // 4. Construir e executar o SQL
        // Ex: ALTER TABLE `Configurações` MODIFY COLUMN `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '...'
        $sql = "ALTER TABLE `$tabela` MODIFY COLUMN `$coluna` $tipo $nulo $padrao $extra COMMENT '$comentarioEscapado'";
        
        // Nota: Se a coluna for Primary Key, alguns bancos exigem remoção da PK antes de alterar, 
        // mas geralmente para comments de campos int auto_increment funciona direto.
        // Se der erro de sintaxe SQL, pode ser necessário ajustar a string de definição acima.
        
        if (mysqli_query($conecta, $sql)) {
            $resposta = array('status' => 'sucesso', 'mensagem' => 'Visibilidade atualizada.');
        } else {
            $resposta = array('status' => 'erro', 'mensagem' => 'Erro SQL: ' . mysqli_error($conecta));
        }
    } else {
        $resposta = array('status' => 'erro', 'mensagem' => 'Coluna não encontrada.');
    }
}

echo json_encode($resposta);
?>