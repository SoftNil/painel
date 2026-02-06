<?php


// Array para armazenar as tabelas que devem ser listadas
 $tabelasParaListar = [];

// 1. Buscar todas as tabelas do banco
 $queryTabelas = mysqli_query($conecta, "SHOW TABLES");

while ($rowTabela = mysqli_fetch_array($queryTabelas)) {
    $nomeTabela = $rowTabela[0];
    
    // 2. Buscar as colunas da tabela para encontrar a Auto Incremento
    // Usamos "SHOW FULL COLUMNS" para ter acesso à coluna 'Comment'
    $queryColunas = mysqli_query($conecta, "SHOW FULL COLUMNS FROM $nomeTabela");
    
    $colunaAutoIncrement = null;
    $comentarioColuna = '';
    $definicaoColuna = array(); // Para salvar detalhes caso precise reconstruir a coluna depois

    if ($queryColunas) {
        while ($rowColuna = mysqli_fetch_assoc($queryColunas)) {
            // Verifica se é auto increment
            if (strpos($rowColuna['Extra'], 'auto_increment') !== false) {
                $colunaAutoIncrement = $rowColuna['Field'];
                $comentarioColuna = $rowColuna['Comment'];
                $definicaoColuna = $rowColuna;
                break; // Encontrou a coluna chave, para o loop interno
            }
        }
    }

    // 3. Verificar se o comentário contém o padrão esperado
    // O padrão deve conter 'visible=' para aparecer na lista
    if ($colunaAutoIncrement && !empty($comentarioColuna)) {
        // Verifica se tem visible=true ou visible=false
        if (preg_match('/visible=(true|false)/i', $comentarioColuna)) {
            
            // Extrair o nome de exibição (ex: tb:tabela=Configurações)
            $nomeExibicao = $nomeTabela; // Padrão caso não ache o padrão
            if (preg_match('/tb:tabela=([^|]+)/i', $comentarioColuna, $matches)) {
                $nomeExibicao = $matches[1];
            }

            // Extrair o valor atual do visible
            $isVisible = false;
            if (preg_match('/visible=true/i', $comentarioColuna)) {
                $isVisible = true;
            }

            // Adiciona ao array de dados
            $tabelasParaListar[] = [
                'nome_bd' => $nomeTabela,
                'nome_exibicao' => $nomeExibicao,
                'coluna_chave' => $colunaAutoIncrement,
                'visible' => $isVisible,
                'comentario_original' => $comentarioColuna
            ];
        }
    }
}
?>


    <style>
       
       

        .checkbox-container { text-align: center; }
        input[type="checkbox"] { transform: scale(1.5); cursor: pointer; }
        .status-msg { margin-left: 10px; font-size: 0.9em; color: green; display: none; }
    </style>

    <table class="table table-bordered w-100 table-sm">
        <thead>
            <tr>
                <th>Nome</th>
                <th style="width: 100px; text-align: center;">Visível</th>
                <th style="width: 100px; text-align: center;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($tabelasParaListar) > 0): ?>
                <?php foreach ($tabelasParaListar as $item): ?>
                    <tr data-tabela="<?php echo $item['nome_bd']; ?>" data-coluna="<?php echo $item['coluna_chave']; ?>">
                        <td><?php echo htmlspecialchars($item['nome_exibicao']); ?></td>                       
                        <td class="checkbox-container">
                            <input type="checkbox" class="check-visible" 
                                <?php echo $item['visible'] ? 'checked' : ''; ?>></br>
                            <span class="status-msg">Salvo!</span>
                        </td>
                        <td class="text-center">
                            <a class="btn btn-sm btn-warning" title="Editar" href="<?php echo $dominio ?>/app/super/dados/<?php echo criptografa($item['nome_bd']); ?>" role="button"><i class="ri-pencil-fill"></i></a>
                        <button class="btn btn-sm btn-danger btn-deletarTabela ms-1" data-id="<?= $r[$pk] ?>" data-bs-toggle="modal" data-bs-target="#modalDeleteTabela" title="Excluir"><i class="ri-delete-bin-7-fill"></i></button>
                    </td>
                    
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2">Nenhuma tabela encontrada com a configuração de comentário esperada.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.check-visible').change(function() {
                const checkbox = $(this);
                const novaVisibilidade = checkbox.prop('checked'); // true ou false
                const tabela = checkbox.closest('tr').data('tabela');
                const coluna = checkbox.closest('tr').data('coluna');
                const msg = checkbox.siblings('.status-msg');

                // Feedback visual de carregamento (opcional)
                msg.css('color', 'blue').text('Salvando...').show();

                $.ajax({
                    url: 'atualiza_visibilidade.php',
                    type: 'POST',
                    data: {
                        tabela: tabela,
                        coluna: coluna,
                        visible: novaVisibilidade
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'sucesso') {
                            msg.css('color', 'green').text('Salvo!');
                            setTimeout(() => { msg.fadeOut(); }, 2000);
                        } else {
                            msg.css('color', 'red').text('Erro: ' + response.mensagem);
                            // Reverte o checkbox se der erro
                            checkbox.prop('checked', !novaVisibilidade);
                        }
                    },
                    error: function() {
                        msg.css('color', 'red').text('Erro na requisição AJAX.');
                        checkbox.prop('checked', !novaVisibilidade);
                    }
                });
            });
        });
    </script>
