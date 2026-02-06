<?php
include '../conexao/conecta.php';

// Definir cabeçalho como JSON para o Ajax entender corretamente
header('Content-Type: application/json');

 $resposta = array();

// --- LÓGICA 1: DELETAR TABELA ---
if (isset($_POST['tb'])) {
    $tb = $_POST['tb'];

    // CORREÇÃO 1: Usar crases `` ` `` em vez de aspas simples ' '
    // O IF EXISTS já trata o caso da tabela não existir, então o check de colunas anterior era desnecessário.
    $sql = "DROP TABLE IF EXISTS `$tb`";

    if (mysqli_query($conecta, $sql)) {
        $resposta = array('success' => true, 'message' => 'Tabela ' . $tb . ' deletada com sucesso!');
    } else {
        $resposta = array('success' => false, 'message' => 'Erro SQL: ' . mysqli_error($conecta));
    }

    // CORREÇÃO 2: Dar exit para parar o script aqui e não executar o resto do código
    echo json_encode($resposta);
    exit;
}

// --- LÓGICA 2: ATUALIZAR VISIBILIDADE DA COLUNA ---
// Só executa se não for deleção de tabela
 $tabela = isset($_POST['tabela']) ? $_POST['tabela'] : '';
 $coluna = isset($_POST['coluna']) ? $_POST['coluna'] : '';
 $visibleValue = isset($_POST['visible']) && $_POST['visible'] === 'true' ? 'true' : 'false';

if ($tabela && $coluna) {
    // 1. Obter informações completas da coluna atual
    $queryColuna = mysqli_query($conecta, "SHOW FULL COLUMNS FROM `$tabela` WHERE Field = '$coluna'");

    if ($queryColuna && mysqli_num_rows($queryColuna) > 0) {
        $dadosColuna = mysqli_fetch_assoc($queryColuna);
        $comentarioAtual = $dadosColuna['Comment'];

        // 2. Atualizar a string do comentário
        $novoComentario = preg_replace('/\bvisible=(true|false)\b/i', 'visible=' . $visibleValue, $comentarioAtual);

        if ($novoComentario === null) {
            $novoComentario = $comentarioAtual;
        }

        // 3. Montar a definição da coluna
        $tipo = $dadosColuna['Type'];
        $nulo = ($dadosColuna['Null'] === 'NO') ? 'NOT NULL' : 'NULL';
        $extra = $dadosColuna['Extra']; 
        
        $padrao = '';
        if ($dadosColuna['Default'] !== null) {
            $padrao = "DEFAULT '" . mysqli_real_escape_string($conecta, $dadosColuna['Default']) . "'";
        } elseif ($dadosColuna['Default'] === 'NULL' && $dadosColuna['Null'] === 'YES') {
            $padrao = "DEFAULT NULL";
        }

        $comentarioEscapado = mysqli_real_escape_string($conecta, $novoComentario);

        // 4. Construir e executar o SQL
        $sql = "ALTER TABLE `$tabela` MODIFY COLUMN `$coluna` $tipo $nulo $padrao $extra COMMENT '$comentarioEscapado'";

        if (mysqli_query($conecta, $sql)) {
            $resposta = array('success' => true, 'message' => 'Visibilidade atualizada.');
        } else {
            $resposta = array('success' => false, 'message' => 'Erro SQL: ' . mysqli_error($conecta));
        }
    } else {
        $resposta = array('success' => false, 'message' => 'Coluna não encontrada.');
    }
} else {
    $resposta = array('success' => false, 'message' => 'Dados inválidos (tabela ou coluna faltando).');
}

echo json_encode($resposta);
?>