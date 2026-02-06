<?php

$uri_path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$segments = explode('/', trim((string)$uri_path, '/'));
$acao = $segments[5] ?? '';

if ($acao === 'salvar') {
    include '../conexao/conecta.php';
    include '../funcoes.php';

    header('Content-Type: application/json; charset=UTF-8');
} else {
    include 'topo.php';
}

$pag = PegaLink(5) ?? '';

if ($pag === '') {
    $pag = 1;
}

$link = $dominio . '/app/' . $_SESSION['nivel'] . '/dados/' . PegaLink(4);

$tabela = descriptografa(PegaLink(4));
if (PegaLink(4) == '') {
    echo '<div class="alert alert-danger" role="alert" data-pg-collapsed>
           <h1><i class="ri-error-warning-line"></i> Tabela não informada.</h1>
        </div>';
}
if ($tabela == false && PegaLink(4) != '') {
    echo '<div class="alert alert-danger" role="alert" data-pg-collapsed>
          <h1><i class="ri-error-warning-line"></i> Tabela inválida.</h1>
        </div>';
}

if ($tabela != false) {
    $pk = 'id';

    // ===== DESCOBRIR CAMPOS =====
    $campos   = [];
    $tipos    = [];
    $nulls    = [];
    $classes  = [];
    $enums    = [];
    $fks      = [];
    $fkcm     = [];
    $cm       = [];
    $tb       = [];
    $modo     = [];

    $qTabelas = mysqli_query($conecta, "SHOW TABLES LIKE '$tabela'");
    $total_tabelas = mysqli_num_rows($qTabelas);


    if ($total_tabelas == 0) {
        echo '<div class="alert alert-danger" role="alert" data-pg-collapsed>
              <h1><i class="ri-error-warning-line"></i> Tabela não encontrada.</h1>
            </div>';
    }
    if ($total_tabelas != 0) {
        $qCampos = mysqli_query($conecta, "SHOW FULL COLUMNS FROM $tabela");
        while ($c = mysqli_fetch_assoc($qCampos)) {
            $campo = $c['Field'];

            if ($c['Key'] === 'PRI') {
                $pk = $campo;
            }

            $tipo  = strtolower($c['Type']);

            $campos[] = $campo;
            $nulls[$campo]   = ($c['Null'] === 'YES');
            $classes[$campo] = trim($c['Comment']);

            if (strpos($classes[$campo], 'fk:') === 0) {
                $modo[$campo] = 'fk';
                [$x, $cfg] = explode(':', $classes[$campo], 2);
                $params = [];
                foreach (explode('|', $cfg) as $part) {
                    if (strpos($part, '=') !== false) {
                        [$k, $v] = explode('=', $part, 2);
                        $params[$k] = $v;
                    }
                }
                $ftable      = $params['tabela'] ?? null;
                $fid         = $params['chave']    ?? null;
                $fcampo      = $params['campo']   ?? null;
                $flabel      = $params['label']   ?? null;
                $feditavel   = $params['editavel'] ?? null;
                $fgrade     = $params['grade'] ?? null;
                $fform       = $params['form'] ?? null;
                $fks[$campo] = compact('ftable', 'fid', 'fcampo', 'flabel', 'feditavel', 'fgrade', 'fform');
                //  continue;
            }

            // Campo via COMMENT
            if (strpos($classes[$campo], 'cm:') === 0) {
                $modo[$campo] = 'cm';
                [$x, $cfg] = explode(':', $classes[$campo], 2);
                $params = [];
                foreach (explode('|', $cfg) as $part) {
                    if (strpos($part, '=') !== false) {
                        [$k, $v] = explode('=', $part, 2);
                        $params[$k] = $v;
                    }
                }
                $clabel        = $params['label'] ?? null;
                $ceditavel     = $params['editavel']    ?? null;
                $cgrade        = $params['grade']   ?? null;
                $cform         = $params['form'] ?? null;
                $cmascara      = $params['mascara'] ?? null;
                $cgrupo        = $params['grupo'] ?? null;
                $cm[$campo]    = compact('clabel', 'ceditavel', 'cgrade', 'cform', 'cmascara', 'cgrupo');
                //  continue;
            }

            // Tabela via COMMENT
            if (strpos($classes[$campo], 'tb:') === 0) {
                $modo[$campo] = 'tb';
                [$x, $cfg] = explode(':', $classes[$campo], 2);
                $params = [];
                foreach (explode('|', $cfg) as $part) {
                    if (strpos($part, '=') !== false) {
                        [$k, $v] = explode('=', $part, 2);
                        $params[$k] = $v;
                    }
                }
                $tlabel   = $params['tabela'] ?? null;
                $tadd     = $params['add']    ?? null;
                $tedit    = $params['edit']   ?? null;
                $tdelete  = $params['delete'] ?? null;
                $tb[$campo] = compact('tlabel', 'tadd', 'tedit', 'tdelete');
                //  continue;
            }

            // ENUM
            if (strpos($tipo, 'enum(') !== false) {
                $tipos[$campo] = 'enum';
                preg_match("/enum\((.*)\)/", $tipo, $m);
                $enums[$campo] = str_getcsv($m[1], ',', "'");
                continue;
            }
            if (strpos($tipo, 'tinyint(1)') !== false || $tipo == 'boolean')      $tipos[$campo] = 'boolean';
            elseif (strpos($tipo, 'int') !== false)         $tipos[$campo] = 'number';
            elseif (strpos($tipo, 'decimal') !== false || strpos($tipo, 'float') !== false || strpos($tipo, 'double') !== false)  $tipos[$campo] = 'decimal';
            elseif (strpos($tipo, 'datetime') !== false || strpos($tipo, 'timestamp') !== false) $tipos[$campo] = 'datetime';
            elseif (strpos($tipo, 'date') !== false)        $tipos[$campo] = 'date';
            elseif (strpos($tipo, 'time') !== false)        $tipos[$campo] = 'time';
            elseif (strpos($tipo, 'text') !== false)        $tipos[$campo] = 'text';
            else                                            $tipos[$campo] = 'varchar';
        }

        // carregar dados das FKs
        $fkData = [];
        $fkComments = [];
        foreach ($fks as $campo => $cfg) {
            $q = mysqli_query($conecta, "SELECT {$cfg['fid']}, {$cfg['fcampo']} FROM {$cfg['ftable']} ORDER BY {$cfg['fcampo']}");
            while ($r = mysqli_fetch_assoc($q)) {
                $fkData[$campo][] = $r;
            }

            // ===== COMMENTS DA TABELA FK (metadados) =====
            $qCols = mysqli_query(
                $conecta,
                "SHOW FULL COLUMNS FROM {$cfg['ftable']}"
            );

            while ($col = mysqli_fetch_assoc($qCols)) {
                $fkComments[$campo][$col['Field']] = trim($col['Comment']);
            }
        }

        // carregar dados da tb
        foreach ($campos as $c) {
            if ($modo[$c] == 'tb') {
                $labeltabela = $tb[$c]['tlabel'];
                $editavel = $tb[$c]['tedit'];
                $adicionavel = $tb[$c]['tadd'];
                $deletavel = $tb[$c]['tdelete'];
            }
        }

        // ===== AÇÕES =====
        $acao = PegaLink(6) ?? '';

        if ($acao == 'salvar') {

            header('Content-Type: application/json; charset=UTF-8');

            $id = $_POST[$pk] ?? '';
            $dados = [];

            foreach ($campos as $c) {

                // IGNORA PK
                if ($c === $pk) continue;

                // IGNORAR TB
                if (($modo[$c] ?? '') === 'tb') continue;

                $valor = $_POST[$c] ?? null;

                if ($valor === null || $valor === '') {
                    if ($nulls[$c]) {
                        $dados[$c] = 'NULL';
                        continue;
                    }
                }


                if ($tipos[$c] == 'boolean') {
                    $dados[$c] = isset($_POST[$c]) ? 1 : 0;
                    continue;
                }

                if ($modo[$c] == 'cm') {
                    switch (strtolower($cm[$c]['cmascara'])) {
                        case 'money':
                        case 'money2':
                            $valor = moeda($valor);
                            break;
                        case 'data':
                            $valor = dataPBanco($valor);
                            break;
                        case 'data_hora':
                            $valor = dataPBancoTime($valor);
                            break;
                    }
                }

                $dados[$c] = mysqli_real_escape_string($conecta, $valor);
            }

            if ($id == '') {

                // $cols = implode(',', array_keys($dados));
                $cols = [];
                $vals = [];

                foreach ($dados as $c => $v) {
                    $cols[] = $c;
                    $vals[] = ($v === 'NULL') ? 'NULL' : "'$v'";
                }

                $sql = "INSERT INTO $tabela (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";

                $q = mysqli_query($conecta, $sql);

                if (!$q) {
                    echo json_encode([
                        'status' => 'erro',
                        'msg' => mysqli_error($conecta),
                        'sql' => $sql
                    ]);
                    exit;
                }
            } else {

                $set = [];
                foreach ($dados as $c => $v) {
                    $set[] = "$c='$v'";
                }

                $sql = "UPDATE $tabela SET " . implode(',', $set) . " WHERE $pk='$id'";
                $q = mysqli_query($conecta, $sql);

                if (!$q) {
                    echo json_encode([
                        'status' => 'erro',
                        'msg' => mysqli_error($conecta),
                        'sql' => $sql
                    ]);
                    exit;
                }
            }

            echo json_encode([
                'status' => 'ok'
            ]);
            exit;
        }

        if ($acao == 'excluir') {

            header('Content-Type: application/json');

            $id = intval($_POST['id'] ?? 0);

            if ($id <= 0) {
                echo json_encode([
                    'status' => 'erro',
                    'msg' => 'ID inválido'
                ]);
                exit;
            }

            $ok = mysqli_query(
                $conecta,
                "DELETE FROM $tabela WHERE $pk='$id'"
            );

            if ($ok) {
                echo json_encode([
                    'status' => 'ok'
                ]);
            } else {
                echo json_encode([
                    'status' => 'erro',
                    'msg' => mysqli_error($conecta)
                ]);
            }

            exit;
        }


        // ===== PESQUISA (BUG CORRIGIDO) =====
        //$busca = PegaLink(6) ?? '';
        $where = '';
        if ($busca != '' || $busca != 'salvar' || $busca != 'excluir') {
            $like = [];
            $b = mysqli_real_escape_string($conecta, $busca);
            foreach ($campos as $c) $like[] = "$c LIKE '%$b%'";
            $where = 'WHERE ' . implode(' OR ', $like);
        }

        // ===== PAGINAÇÃO =====
        $limite = 10;
        $pagina = max(1, intval($pag ?? 1));
        $inicio = ($pagina - 1) * $limite;

        $totalQ = mysqli_query($conecta, "SELECT COUNT(*) t FROM $tabela $where");
        $total = mysqli_fetch_assoc($totalQ)['t'];
        $paginas = ceil($total / $limite);

        $lista = mysqli_query($conecta, "SELECT * FROM $tabela $where LIMIT $inicio,$limite");
?>
        <script src="<?php echo $dominio ?>/app/plugins/js/jquery-3.7.1.min.js"></script>
        <script src="<?php echo $dominio ?>/app/plugins/js/mask/jquery.mask.js"></script>
        <link rel="stylesheet" href="<?php echo $dominio ?>/app/css/imagem.css">
        <div class="container">
            <h3 class="mb-3"><?php echo $labeltabela; ?></h3>
            <form class="row mb-3">
                <div class="col-md-4">
                    <input type="text" name="busca" value="<?php echo $busca; ?>" class="form-control" placeholder="Pesquisar">
                </div>
                <div class="col-md-2"><button class="btn btn-primary">Pesquisar</button></div>
                <?PHP if ($adicionavel != 'false') { ?>
                    <div class="col-md-6 text-end"><button type="button" class="btn btn-success" title="Adicionar" data-bs-toggle="modal" data-bs-target="#modalForm"><i class="ri-file-add-fill"></i> Adicionar</button></div>
                <?PHP } ?>
            </form>

            <div id="menssagem_dados" class="text-center "></div>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr class="text-center">
                        <?php foreach ($campos as $c) {
                            if ($modo[$c] == 'cm') {
                                if ($cm[$c]['cgrade'] != 'false') {
                                    echo "<th>" . $cm[$c]['clabel'] . "</th>";
                                }
                            }
                            if ($modo[$c] == 'fk') {
                                if ($fks[$c]['fgrade'] != 'false') {
                                    echo "<th>" .  $fks[$c]['flabel'] . "</th>";
                                }
                            }
                        } ?><th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($r = mysqli_fetch_assoc($lista)) { ?>
                        <tr>
                            <?php foreach ($campos as $c) {

                                if ($modo[$c] == 'cm') {
                                    if ($cm[$c]['cgrade'] != 'false') {
                                        if ($cm[$c]['cmascara'] == 'money') {
                                            echo '<td class="text-end"> R$ ' . PontoPorVirgula($r[$c]) . '</td>';
                                        } elseif ($cm[$c]['cmascara'] == 'money2') {
                                            echo '<td class="text-end"> R$ ' . PontoPorVirgula($r[$c]) . '</td>';
                                        } elseif ($cm[$c]['cmascara'] == 'data') {
                                            echo '<td class="text-center">' . dataPFora($r[$c]) . '</td>';
                                        } elseif ($cm[$c]['cmascara'] == 'data_hora') {
                                            echo '<td class="text-center">' . dataPForaTime($r[$c]) . '</td>';
                                        } elseif ($cm[$c]['cmascara'] == 'cep') {
                                            echo '<td class="text-center">' . $r[$c] . '</td>';
                                        } elseif ($cm[$c]['cmascara'] == 'cpf') {
                                            echo '<td class="text-center">' . $r[$c] . '</td>';
                                        } elseif ($cm[$c]['cmascara'] == 'cnpj') {
                                            echo '<td class="text-center">' . $r[$c] . '</td>';
                                        } elseif ($cm[$c]['cmascara'] == 'telefone') {
                                            echo '<td class="text-center">' . $r[$c] . '</td>';
                                        } elseif ($cm[$c]['cmascara'] == 'celular') {
                                            echo '<td class="text-center">' . $r[$c] . '</td>';
                                        } elseif ($cm[$c]['cmascara'] == 'escolha') {
                                            echo '<td class="text-center">' . $r[$c] . '</td>';
                                        } elseif ($cm[$c]['cmascara'] == 'imagem') {
                                            echo '<td class="text-center"><img src="' . imgExiste($dominio, '/app/imagens/uploads/', $r[$c]) . '" alt="" height="30"></td>';
                                        } elseif ($cm[$c]['cmascara'] == 'normal') {

                                            if ($tipos[$c] == 'number') {
                                                echo '<td class="text-center">' . $r[$c] . '</td>';
                                            } elseif ($tipos[$c] == 'boolean') {
                                                if ($r[$c] == 1) {
                                                    echo '<td class="text-center"><i class="ri-checkbox-line"></i></td>';
                                                } else {
                                                    echo '<td class="text-center"><i class="ri-checkbox-blank-line"></i></td>';
                                                }
                                            } elseif ($tipos[$c] == 'date') {
                                                echo '<td class="text-center">' . dataPFora($r[$c]) . '</td>';
                                            } elseif ($tipos[$c] == 'datetime') {
                                                echo '<td class="text-center">' . dataPForaTime($r[$c]) . '</td>';
                                            } elseif ($tipos[$c] == 'time') {
                                                echo '<td class="text-center">' . $r[$c] . '</td>';
                                            } elseif ($tipos[$c] == 'enum') {
                                                echo '<td class="text-center">' . $r[$c] . '</td>';
                                            } elseif ($tipos[$c] == 'text') {
                                                if (mb_strlen($r[$c]) > 20) {
                                                    echo '<td class="text-center">' . mb_substr($r[$c], 0, 20) . '...' . '</td>';
                                                } else {
                                                    echo '<td class="text-center">' . $r[$c] . '</td>';
                                                }
                                            } elseif ($tipos[$c] == 'varchar') {
                                                echo '<td class="text-center">' . $r[$c] . '</td>';
                                            } elseif ($tipos[$c] == 'decimal') {
                                                echo '<td class="text-end">' . $r[$c] . '</td>';
                                            }
                                        } else echo '<td>' . $r[$c] . '</td>';
                                    }
                                } elseif ($modo[$c] == 'fk') {
                                    foreach ($fkData[$c] as $op) {
                                        if ($fks[$c]['fgrade'] != 'false') {
                                            if ($op[$fks[$c]['fid']] == $r[$c]) {
                                                echo '<td class="text-center">' . $op[$fks[$c]['fcampo']] . '</td>';
                                            } else {
                                                echo '<td></td>';
                                            }
                                        }
                                    }
                                }
                            }
                            ?>
                            <td class="text-center">
                                <?php if ($editavel != 'false') { ?>
                                    <button class="btn btn-sm btn-warning" title="Editar" onclick='editar(<?php echo htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8'); ?>)'> <i class="ri-pencil-fill"></i></button>
                                <?php } ?>
                                <?php if ($deletavel != 'false') { ?>
                                    <button class="btn btn-sm btn-danger btn-deletarDados" data-id="<?= $r[$pk] ?>" data-bs-toggle="modal" data-bs-target="#modalDeleteDados" title="Excluir"><i class="ri-delete-bin-7-fill"></i></button>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <?php
            $qtdNav = 3;
            if ($pag > $paginas) {
                $pag = $paginas;
            }
            $Previous = $pag - 1;
            $Next = $pag + 1;
            if ($pag == $paginas) {
                $NextDisable = 'disabled';
            }
            if ($pag == 1) {
                $PreviousDisable = 'disabled';
            }
            ?>
            <div class="w-100" data-pg-collapsed>
                <nav class="w-100" aria-label="Paginação" data-pg-collapsed>
                    <ul class="pagination justify-content-center ">
                        <?php
                        if ($paginas > 1 && $pag <= $paginas) {
                        ?>
                            <li class="page-item">
                                <a class="page-link fw-bold <?php echo $PreviousDisable; ?>" href="<?php echo $link . '/' . $Previous . '/' . $busca; ?>" tabindex="-1" aria-disabled="true" aria-label="Anterior"><span class="pg-texto"><i class="ri-arrow-left-s-line"></i></span></a>
                            </li>
                            <?php
                            for ($i = 1; $i <= $paginas; $i++) {
                                if ($i == $pag) {
                            ?>
                                    <li class="page-item">
                                        <a class="page-link fw-bold active" aria-current="page" href=""><?php echo $i; ?></a>
                                    </li>
                                    <?php
                                } else if ($i < ($pag - $qtdNav) && $i != 1) {
                                    if (!$dottedBefore) {
                                    ?>
                                        <li class="page-item">
                                            <a class="page-link fw-bold" href="<?php echo $link . '/' . ($pag - $qtdNav - 1) . '/' . $busca ?>"><span class="pg-texto">[...]</span></a>
                                        </li>
                                    <?php
                                        $dottedBefore = true;
                                    }
                                } else if ($i > ($pag + $qtdNav) && $i < $paginas) {
                                    if (!$dottedAfter) {
                                    ?>
                                        <li class="page-item">
                                            <a class="page-link fw-bold" href="<?php echo  $link . '/' . ($pag + $qtdNav + 1) . '/' . $busca ?>"><span class="pg-texto">[...]</span></a>
                                        </li>
                                    <?php
                                        $dottedAfter = true;
                                    }
                                } else {
                                    ?>
                                    <li class="page-item">
                                        <a class="page-link fw-bold" href="<?php echo $link . '/' . $i . '/' . $busca ?>"><span class="pg-texto"><?php echo $i; ?></span></a>
                                    </li>
                            <?php
                                }
                            }
                            ?>
                            <li class="page-item">
                                <a class="page-link fw-bold <?php echo $NextDisable; ?>" href="<?php echo $link . '/' . $Next . '/' . $busca; ?>" aria-label="Próxima"><span class="pg-texto"><i class="ri-arrow-right-s-line"></i></span></a>
                            </li>
                        <?php
                        }
                        ?>
                    </ul>
                </nav>
            </div>

        </div>

        <div class="modal fade" id="modalForm">
            <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable ">
                <div class="modal-content">
                    <form id="formCrud">
                        <div class="modal-header">
                            <h5><?php echo $labeltabela; ?></h5>
                            <div id="menssagem_salvar" class="text-center me-5 ms-5 w-100" style="position: relative; top: -8px; margin-bottom: -30px;"></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <?php
                                foreach ($campos as $c) {
                                    $label = isset($cm[$c]['clabel']) ? $cm[$c]['clabel'] : (isset($fks[$c]) ? $fks[$c]['flabel'] : ucfirst($c));

                                    if (isset($cm[$c]['cform']) && $cm[$c]['cform'] == 'false') {
                                ?>
                                        <input type="hidden" name="<?php echo $c; ?>" id="<?php echo $c; ?>" class="form-control <?php echo $cm[$c]['cmascara'] ?? ''; ?>">
                                    <?php
                                        continue;
                                    }

                                    if (isset($fks[$c]['fform']) && $fks[$c]['fform'] == 'false') {
                                    ?>
                                        <input type="hidden" name="<?php echo $c; ?>" id="<?php echo $c; ?>" class="form-control <?php echo $cm[$c]['cmascara'] ?? ''; ?>">
                                    <?php
                                        continue;
                                    }

                                    if ($c === $pk) { ?>
                                        <input type="hidden" name="<?php echo $c; ?>" id="<?php echo $c; ?>" class="form-control">
                                    <?php
                                        continue;
                                    } elseif ($modo[$c] == 'fk') { ?>
                                        <div class="col-md-6">
                                            <label for="<?php echo $c; ?>" class="form-label"><?php echo $label; ?></label>
                                            <select name="<?php echo $c; ?>" id="<?php echo $c; ?>" class="form-select" <?php echo !$nulls[$c] ? 'required' : ''; ?>>
                                                <option value="">Selecione...</option>
                                                <?php
                                                if (isset($fkData[$c])) {
                                                    foreach ($fkData[$c] as $op) {
                                                        $sel = "";
                                                        echo "<option value='{$op[$fks[$c]['fid']]}' $sel>{$op[$fks[$c]['fcampo']]}</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    <?php } elseif ($modo[$c] == 'cm') {
                                        $isText = ($tipos[$c] == 'text');
                                        $isNumber = ($tipos[$c] == 'number');
                                        $isBool = ($tipos[$c] == 'boolean');
                                        $isDate = ($tipos[$c] == 'date');
                                        $isTime = ($tipos[$c] == 'time');
                                        $isEnum = ($tipos[$c] == 'enum');
                                        $isDateTime = ($tipos[$c] == 'datetime');
                                        $isImg = (isset($cm[$c]['cmascara']) && $cm[$c]['cmascara'] == 'imagem');
                                        $isRadio = (isset($cm[$c]['cmascara']) && $cm[$c]['cmascara'] == 'escolha');
                                        $isSenha = (isset($cm[$c]['cmascara']) && $cm[$c]['cmascara'] == 'senha');
                                    ?>
                                        <div class="col-md-6 <?php echo $isText ? 'col-lg-12' : 'col-md-6'; ?>">
                                            <label for="<?php echo $c; ?>" class="form-label"><?php echo $label; ?></label>

                                            <?php if ($isBool) { ?>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="<?php echo $c; ?>" id="<?php echo $c; ?>">
                                                </div>
                                                <?php } elseif ($isRadio) {
                                                $query_9 = "SELECT * FROM grupo_9 WHERE grupo_9 = '{$cm[$c]['cgrupo']}'";
                                                $sql_9 = mysqli_query($conecta, $query_9);

                                                $grupos_9 = '';
                                                while ($linha_9 = mysqli_fetch_assoc($sql_9)) {
                                                    $grupos_9 = $linha_9['grupos_9'];
                                                }

                                                if (!empty($grupos_9)) {
                                                    foreach (explode('|', $grupos_9) as $op) {
                                                        $idUnico = preg_replace('/[^a-zA-Z0-9_-]/', '_', $c . '_' . $op);
                                                ?>
                                                        <div class="form-check" data-pg-collapsed>
                                                            <input class="form-check-input" type="radio" name="<?php echo $c; ?>" id="<?php echo $idUnico; ?>" value="<?php echo $op; ?>">
                                                            <label class="form-check-label" for="<?php echo $idUnico; ?>">
                                                                <?php echo $op; ?>
                                                            </label>
                                                        </div>
                                                <?php
                                                    }
                                                }
                                            } elseif ($isImg) { ?>
                                                <input type="hidden" name="<?php echo $c; ?>" id="<?php echo $c; ?>" class="form-control <?php echo $classes[$c] ?? ''; ?>" <?php echo !$nulls[$c] ? 'required' : ''; ?>>

                                                <div class="position-relative text-center imagem-preview" onclick="abrirModalImagens('<?php echo $c; ?>')">
                                                    <input type="text" class="form-control pe-5" style="height: 100px; cursor: pointer;" id="campoImagem">
                                                    <img id="preview_<?php echo $c; ?>" class="imagem-form input-imagem" src="" alt="" height="100">
                                                </div>

                                            <?php } elseif ($isEnum) { ?>
                                                <select name="<?php echo $c; ?>" id="<?php echo $c; ?>" class="form-select <?php echo $classes[$c] ?? ''; ?>" <?php echo !$nulls[$c] ? 'required' : ''; ?>>
                                                    <option value="">Selecione</option>
                                                    <?php foreach ($enums[$c] as $op) { ?>
                                                        <option value="<?php echo $op; ?>"><?php echo ucfirst($op); ?></option>
                                                    <?php } ?>
                                                </select>

                                            <?php } elseif ($isText) { ?>
                                                <textarea name="<?php echo $c; ?>" id="<?php echo $c; ?>" class="form-control" rows="3"></textarea>

                                            <?php } elseif ($isNumber) { ?>
                                                <input type="number" name="<?php echo $c; ?>" id="<?php echo $c; ?>" class="form-control <?php echo $cm[$c]['cmascara'] ?? ''; ?>" <?php echo !$nulls[$c] ? 'required' : ''; ?>>

                                            <?php } elseif ($isDate) { ?>
                                                <input type="date" name="<?php echo $c; ?>" id="<?php echo $c; ?>" class="form-control <?php echo $cm[$c]['cmascara'] ?? ''; ?>" <?php echo !$nulls[$c] ? 'required' : ''; ?>>

                                            <?php } elseif ($isTime) { ?>
                                                <input type="time" name="<?php echo $c; ?>" id="<?php echo $c; ?>" class="form-control <?php echo $cm[$c]['cmascara'] ?? ''; ?>" <?php echo !$nulls[$c] ? 'required' : ''; ?>>

                                            <?php } elseif ($isDateTime) { ?>
                                                <input type="datetime" name="<?php echo $c; ?>" id="<?php echo $c; ?>" class="form-control <?php echo $cm[$c]['cmascara'] ?? ''; ?>" <?php echo !$nulls[$c] ? 'required' : ''; ?>>

                                            <?php } elseif ($isSenha) { ?>
                                                <input type="password" name="<?php echo $c; ?>" id="<?php echo $c; ?>" class="form-control <?php echo $cm[$c]['cmascara'] ?? ''; ?>" <?php echo !$nulls[$c] ? 'required' : ''; ?>>

                                            <?php } else { ?>
                                                <!-- Inputs padrão (text, number, date) -->
                                                <input type="text" name="<?php echo $c; ?>" id="<?php echo $c; ?>" class="form-control <?php echo $cm[$c]['cmascara'] ?? ''; ?>" <?php echo !$nulls[$c] ? 'required' : ''; ?>>
                                            <?php } ?>
                                        </div>
                                <?php }
                                }
                                ?>
                            </div>
                        </div>
                        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button class="btn btn-primary" type="button" onclick="salvarAjax()">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalDeleteDados" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content rounded-4 shadow">
                    <div class="modal-body p-4 text-center">

                        <h5 class="mb-0">Deseja mesmo excluír este registro?</h5>

                        <p class="mb-0">A acão não podem desfeita. Os registros deletados não poderão ser recuperadas.</p>
                    </div>
                    <div class="modal-footer flex-nowrap p-0 text-center">
                        <div id="btnDeleteDados"></div>
                        <button type="button" class="btn btn-lg btn-link fs-6 text-decoration-none col-6 me-5 rounded-0 btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
<?php
        include '../imagem.php';
    }
}
?>
<script src="<?php echo $dominio ?>/app/plugins/js/mask/masks.js"></script>

<script>
    const modalEl = document.getElementById('modalForm');
    modalEl.addEventListener('show.bs.modal', function(event) {
        if (!modalEl.classList.contains('editando')) {
            const inputs = modalEl.querySelectorAll('input, textarea, select');
            inputs.forEach(el => {
                if (el.type === 'checkbox') {
                    el.checked = false;
                } else if (el.type === 'radio') {
                    el.checked = false;
                } else {
                    el.value = '';
                }
            });
            const previews = modalEl.querySelectorAll('img[id^="preview_"]');
            previews.forEach(img => {
                img.src = '<?php echo $dominio; ?>/app/imagens/site/sem_imagem.png';
                img.style.display = 'none';
            });
            document.getElementById('<?php echo $pk; ?>').value = '';
        }
        modalEl.classList.remove('editando');
    });

    function editar(d) {
        modalEl.classList.add('editando');
        const inputs = modalEl.querySelectorAll('input, textarea, select');
        inputs.forEach(el => {
            if (el.type === 'checkbox') {
                el.checked = false;
            } else if (el.type === 'radio') {
                el.checked = false;
            } else {
                el.value = '';
            }
        });
        <?php foreach ($campos as $c) { ?>
            <?php if (isset($cm[$c]['cmascara']) && $cm[$c]['cmascara'] == 'escolha') { ?>

                const radios<?php echo $c; ?> = document.getElementsByName('<?php echo $c; ?>');
                const valorRadio<?php echo $c; ?> = d['<?php echo $c; ?>'] || '';

                for (let i = 0; i < radios<?php echo $c; ?>.length; i++) {
                    if (radios<?php echo $c; ?>[i].value === valorRadio<?php echo $c; ?>) {
                        radios<?php echo $c; ?>[i].checked = true;
                        break;
                    }
                }

            <?php } else { ?>
                const element<?php echo $c; ?> = document.getElementById('<?php echo $c; ?>');

                if (element<?php echo $c; ?>) {
                    if (element<?php echo $c; ?>.type === 'checkbox') {
                        element<?php echo $c; ?>.checked = d['<?php echo $c; ?>'] == 1;
                    } else {

                        function formatarMoeda(valor) {
                            let str = valor;
                            if (typeof str !== 'string') return str;
                            return str.replace(/\./g, ',');
                        }

                        function formatarData(valor) {
                            let str = valor;
                            if (typeof str !== 'string') return str;
                            const partes = str.split('-');
                            if (partes.length !== 3) return str;
                            return `${partes[2].padStart(2, '0')}/${partes[1].padStart(2, '0')}/${partes[0]}`;
                        }

                        function formatarDataHora(valor) {
                            let str = valor;
                            if (typeof str !== 'string') return str;
                            const [data, hora] = str.split(' ');
                            const partes = data.split('-');
                            if (partes.length !== 3) return str;
                            return `${partes[2].padStart(2, '0')}/${partes[1].padStart(2, '0')}/${partes[0]} ${hora || ''}`;
                        }

                        let valorFinal = d['<?php echo $c; ?>'] || '';

                        <?php if ($classes[$c] == 'money') { ?>
                            valorFinal = formatarMoeda(valorFinal);
                        <?php } elseif ($classes[$c] == 'money2') { ?>
                            valorFinal = formatarMoeda(valorFinal);
                        <?php } elseif ($classes[$c] == 'data') { ?>
                            valorFinal = formatarData(valorFinal);
                        <?php } elseif ($classes[$c] == 'data_hora') { ?>
                            valorFinal = formatarDataHora(valorFinal);
                        <?php } ?>

                        element<?php echo $c; ?>.value = valorFinal;
                    }

                    <?php if (isset($cm[$c]['cmascara']) && $cm[$c]['cmascara'] === 'imagem') { ?>
                        const preview<?php echo $c; ?> = document.getElementById('preview_<?php echo $c; ?>');

                        if (preview<?php echo $c; ?>) {
                            const val = d['<?php echo $c; ?>'] || '';
                            if (val) {
                                preview<?php echo $c; ?>.src = '<?php echo  $dominio; ?>/app/imagens/uploads/' + val;
                                preview<?php echo $c; ?>.style.display = 'inline-block';
                            } else {
                                preview<?php echo $c; ?>.src = '<?php echo  $dominio; ?>/app/imagens/site/sem_imagem.png';
                                preview<?php echo $c; ?>.style.display = 'inline-block';
                            }
                        }
                    <?php } ?>
                }
            <?php } ?>
        <?php } ?>

        const pkElement = document.getElementById('<?php echo $pk; ?>');
        if (pkElement) {
            pkElement.value = d['<?php echo $pk; ?>'];
        }

        new bootstrap.Modal(modalEl).show();
    }

    function salvarAjax() {

        const form = document.getElementById('formCrud');
        const formData = new FormData(form);

        $.ajax({
            url: '<?php echo $link . '/' . $pag; ?>/salvar',
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',

            success: function(resp) {
                console.log(resp);

                if (resp.status === 'ok') {
                    $('#modalForm').modal('hide');
                    $('#menssagem_dados').html('<div class="alert alert-success alert-dismissible fade show" id="alertAuto" role="alert" data-pg-collapsed><i class="ri-check-fill"></i><strong> Sucesso </strong> Dados salvo com sucesso!</div>');
                    setTimeout(function() {
                        if (!modalEl.classList.contains('editando')) {
                            location.reload();
                        } else {
                            window.location.replace('<?php echo $link . '/' . $paginas . '/' . $busca; ?>');
                        }
                    }, 3000);
                } else {
                    $('#modalForm').modal('hide');
                    $('#menssagem_dados').html('<div class="alert alert-danger alert-dismissible fade show" id="alertAuto" role="alert" data-pg-collapsed><i class="ri-error-warning-line"></i><strong> Erro </strong> ' + resp.msg + '</div>');
                    console.log(resp.sql);
                }
            },

            error: function(xhr) {
                console.log(xhr.responseText);
                $('#modalForm').modal('hide');
                $('#menssagem_dados').html('<div class="alert alert-danger alert-dismissible fade show" id="alertAuto" role="alert" data-pg-collapsed><i class="ri-error-warning-line"></i><strong> Erro </strong> fatal no PHP</div>');
            }
        });
        setTimeout(function() {
            $('#alertAuto').fadeOut(400, function() {
                $(this).remove();
            });
        }, 5000);
    }

    $(document).on('click', '.btn-deletarDados', function() {
        let id = $(this).data('id');
        $('#btnDeleteDados').html('<button type="button" class="btn btn-lg btn-link fs-6 text-decoration-none col-6 m-0 rounded-0 border-right btnExcluirDados" data-id="' + id + ' title="Excluir"><strong>Excluír</strong></button>');

    });


    $(document).on('click', '.btnExcluirDados', function() {

        let id = $(this).data('id');

        $.ajax({
            url: '<?php echo $link . '/' . $pag; ?>/excluir',
            type: 'POST',
            data: {
                id,
            },
            success: function(resp) {
                $('#modalDeleteDados').modal('hide');
                $('#menssagem_dados').html('<div class="alert alert-success alert-dismissible fade show" id="alertAuto" role="alert" data-pg-collapsed><i class="ri-check-fill"></i><strong> Sucesso </strong> Excluído com sucesso!</div>');
                setTimeout(function() {
                    location.reload();
                }, 3000);
            },
            error: function() {
                $('#modalDeleteDados').modal('hide');
                $('#menssagem_dados').html('<div class="alert alert-danger alert-dismissible fade show" id="alertAuto" role="alert" data-pg-collapsed><i class="ri-error-warning-line"></i><strong> Erro </strong> Não foi possível excluir o registro</div>');
            }

        });

        setTimeout(function() {
            $('#alertAuto').fadeOut(400, function() {
                $(this).remove();
            });
        }, 5000);

    });
</script>

<?php
include 'rodape.php';
?>