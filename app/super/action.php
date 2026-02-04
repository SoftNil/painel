<?php
// Bloco para executar o SQL via AJAX
if (isset($_POST['action']) && $_POST['action'] === 'exec_sql') {
    header('Content-Type: application/json');
    $sql = $_POST['sql'] ?? '';

    if (!empty($sql)) {
        if ($conecta->multi_query($sql)) {
            do {
                if ($res = $conecta->store_result()) {
                    $res->free();
                }
            } while ($conecta->more_results() && $conecta->next_result());

            echo json_encode(['success' => true, 'message' => 'SQL executado com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro SQL: ' . $conecta->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'SQL vazio.']);
    }
    exit;
}

// Bloco para buscar colunas de uma tabela específica
if (isset($_GET['action']) && $_GET['action'] === 'get_columns') {
    header('Content-Type: application/json');
    $table = $_GET['table'] ?? '';
    $response = ['success' => false, 'columns' => [], 'autoInc' => ''];

    if (!empty($table)) {
        $table = mysqli_real_escape_string($conecta, $table);
        $cols_query = mysqli_query($conecta, "SHOW FULL COLUMNS FROM $table");

        if ($cols_query) {
            $columns = [];
            $autoInc = '';
            while ($c = mysqli_fetch_assoc($cols_query)) {
                $columns[] = $c['Field'];
                if (stripos($c['Extra'], 'auto_increment') !== false) {
                    $autoInc = $c['Field'];
                }
            }
            $response = ['success' => true, 'columns' => $columns, 'autoInc' => $autoInc];
        }
    }
    echo json_encode($response);
    exit;
}

// Buscar lista de grupos (para o select)
if (isset($_GET['action']) && $_GET['action'] === 'get_grupos') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'grupos' => []];

    $query = mysqli_query($conecta, "SELECT DISTINCT grupo_9 FROM grupo_9 ORDER BY grupo_9 ASC");

    if ($query) {
        $grupos = [];
        while ($row = mysqli_fetch_assoc($query)) {
            $val = $row['grupo_9'];
            if (!empty($val)) {
                $grupos[] = $val;
            }
        }
        $response = ['success' => true, 'grupos' => $grupos];
    }
    echo json_encode($response);
    exit;
}

// Buscar detalhes de um grupo específico
if (isset($_GET['action']) && $_GET['action'] === 'get_group_details') {
    header('Content-Type: application/json');
    $groupName = $_GET['name'] ?? '';
    $response = ['success' => false, 'options' => ''];

    if (!empty($groupName)) {
        $groupName = mysqli_real_escape_string($conecta, $groupName);
        $query = mysqli_query($conecta, "SELECT grupos_9 FROM grupo_9 WHERE grupo_9 = '$groupName' LIMIT 1");

        if ($query && $row = mysqli_fetch_assoc($query)) {
            $response = ['success' => true, 'options' => $row['grupos_9']];
        }
    }
    echo json_encode($response);
    exit;
}

// Deletar Grupo (Linha inteira)
if (isset($_POST['action']) && $_POST['action'] === 'delete_group') {
    header('Content-Type: application/json');
    $name = $_POST['name'] ?? '';
    $response = ['success' => false, 'message' => ''];

    if (!empty($name)) {
        $name = mysqli_real_escape_string($conecta, $name);
        $sql = "DELETE FROM grupo_9 WHERE grupo_9 = '$name'";

        if (mysqli_query($conecta, $sql)) {
            $response = ['success' => true, 'message' => 'Grupo excluído com sucesso!'];
        } else {
            $response = ['success' => false, 'message' => 'Erro ao excluir: ' . mysqli_error($conecta)];
        }
    } else {
        $response = ['success' => false, 'message' => 'Nome inválido.'];
    }
    echo json_encode($response);
    exit;
}

// Salvar grupo (Insert, Update ou Rename)
if (isset($_POST['action']) && $_POST['action'] === 'save_group') {
    header('Content-Type: application/json');
    $name = $_POST['name'] ?? '';
    $data = $_POST['data'] ?? '';
    $originalName = $_POST['original_name'] ?? ''; // Para renomear

    $response = ['success' => false, 'message' => ''];

    if (!empty($name)) {
        $name = mysqli_real_escape_string($conecta, $name);
        $data = mysqli_real_escape_string($conecta, $data);
        $originalName = mysqli_real_escape_string($conecta, $originalName);

        // Lógica de Renomear
        if (!empty($originalName) && $originalName !== $name) {
            mysqli_query($conecta, "DELETE FROM grupo_9 WHERE grupo_9 = '$originalName'");
        }

        // Verifica se o nome atual existe
        $check = mysqli_query($conecta, "SELECT grupo_9 FROM grupo_9 WHERE grupo_9 = '$name'");

        if (mysqli_num_rows($check) > 0) {
            $sql = "UPDATE grupo_9 SET grupos_9 = '$data' WHERE grupo_9 = '$name'";
            if (mysqli_query($conecta, $sql)) {
                $response = ['success' => true, 'message' => 'Grupo atualizado com sucesso!'];
            } else {
                $response = ['success' => false, 'message' => 'Erro ao atualizar: ' . mysqli_error($conecta)];
            }
        } else {
            $sql = "INSERT INTO grupo_9 (grupo_9, grupos_9) VALUES ('$name', '$data')";
            if (mysqli_query($conecta, $sql)) {
                $response = ['success' => true, 'message' => 'Grupo salvo com sucesso!'];
            } else {
                $response = ['success' => false, 'message' => 'Erro ao salvar: ' . mysqli_error($conecta)];
            }
        }
    } else {
        $response = ['success' => false, 'message' => 'Nome do grupo inválido.'];
    }

    echo json_encode($response);
    exit;
}
?>