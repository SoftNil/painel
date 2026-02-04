<?php

include '../conexao/conecta.php';
include '../funcoes.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
// tree_editor_admin.php
// API para o Editor de Menu (GET/POST JSON responses)
// Requer /config/conecta.php


header('Content-Type: application/json; charset=utf-8');

// Safety: ensure $conecta exists
if (!isset($conecta) || !($conecta instanceof mysqli)) {
    echo json_encode(["status"=>"error","message"=>"Conexão MySQL não encontrada."]);
    exit;
}
$tabela  = 'menu_5';

// Helper responses
function json_ok($data = []) { echo json_encode(array_merge(["status"=>"ok"], $data)); exit; }
function json_err($msg) { echo json_encode(["status"=>"error","message"=>$msg]); exit; }

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$nivel  = $_REQUEST['nivel']  ?? null;
// ---------- build_tree ----------
function build_tree(array $items, int $parent = 0): array {
    $branch = [];
    foreach ($items as $item) {
        if ((int)$item['parent_id_5'] === $parent) {
            $children = build_tree($items, (int)$item['id_5']);
            $title = htmlspecialchars($item['texto_5'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            // Ícone já vem como HTML do banco (ex: <i class="ri-mail-add-fill"></i>)
            $iconHtml = $item['icone_5'] ? $item['icone_5'] . ' ' : '';
            $node = [
                "key" => (string)$item['id_5'],
                "title" => $iconHtml . $title,
                "data" => [
                    "icone" => $item['icone_5'],
                    "link"  => $item['link_5']
                ],
                "folder" => !empty($children)
            ];
            if (!empty($children)) $node['children'] = $children;
            $branch[] = $node;
        }
    }
    return $branch;
}

// ---------- GET TREE ----------
if ($action === 'get_tree') {
    
    $sql = "SELECT id_5, parent_id_5, icone_5, texto_5, link_5, ordem_5 FROM $tabela WHERE nivel_5 = '$nivel' ORDER BY parent_id_5, ordem_5, texto_5";
    $res = mysqli_query($conecta, $sql);
    if (!$res) json_err("Erro SQL: ".mysqli_error($conecta));
    $items = [];
    while ($r = mysqli_fetch_assoc($res)) $items[] = $r;
    echo json_encode(build_tree($items));
    exit;
}

// ---------- CREATE NODE ----------
if ($action === 'create_node') {
    $parent_id = isset($_POST['parent']) ? intval($_POST['parent']) : 0;
    $title = trim($_POST['title'] ?? 'Novo Item');
    $icone = trim($_POST['icone'] ?? '');
    $link = trim($_POST['link'] ?? '');

    $q = "SELECT COALESCE(MAX(ordem_5),0)+1 AS novo FROM $tabela WHERE parent_id_5 = $parent_id";
    $r = mysqli_query($conecta, $q);
    if (!$r) json_err("Erro SQL ordem: ".mysqli_error($conecta));
    $row = mysqli_fetch_assoc($r);
    $ordem_novo = intval($row['novo']);

    $stmt = mysqli_prepare($conecta, "INSERT INTO $tabela (parent_id_5, icone_5, texto_5, link_5, ordem_5) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) json_err("Erro prepare: ".mysqli_error($conecta));
    mysqli_stmt_bind_param($stmt, "isssi", $parent_id, $icone, $title, $link, $ordem_novo);
    if (mysqli_stmt_execute($stmt)) {
        $new_id = mysqli_insert_id($conecta);
        json_ok(["id" => $new_id]);
    } else json_err("Falha inserir: ".mysqli_error($conecta));
}

// ---------- RENAME / UPDATE ----------
if ($action === 'rename_node') {
    $id = intval($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $icone = array_key_exists('icone', $_POST) ? trim($_POST['icone']) : null;
    $link = array_key_exists('link', $_POST) ? trim($_POST['link']) : null;

    if (!$id || $title === '') json_err("Dados inválidos.");

    if ($icone === null && $link === null) {
        $stmt = mysqli_prepare($conecta, "UPDATE $tabela SET texto_5 = ? WHERE id_5 = ?");
        mysqli_stmt_bind_param($stmt, "si", $title, $id);
    } else {
        $stmt = mysqli_prepare($conecta, "UPDATE $tabela SET icone_5 = ?, texto_5 = ?, link_5 = ? WHERE id_5 = ?");
        mysqli_stmt_bind_param($stmt, "sssi", $icone, $title, $link, $id);
    }
    if (mysqli_stmt_execute($stmt)) json_ok();
    else json_err("Falha atualizar: ".mysqli_error($conecta));
}

// ---------- DELETE NODE (recursivo) ----------
if ($action === 'delete_node') {
    
    $id = intval($_POST['id'] ?? 0);
    if (!$id) json_err("ID inválido.");
    $delete_recursive = function($id_val) use (&$delete_recursive, $conecta , $tabela) {
        $res = mysqli_query($conecta, "SELECT id_5 FROM $tabela WHERE parent_id_5 = ".intval($id_val));
        if ($res) {
            while ($r = mysqli_fetch_assoc($res)) {
                $delete_recursive($r['id_5']);
            }
        }
        mysqli_query($conecta, "DELETE FROM $tabela WHERE id_5 = ".intval($id_val));
    };
    $delete_recursive($id);
    json_ok();
}

// ---------- MOVE NODE ----------
if ($action === 'move_node') {
    $id = intval($_POST['id'] ?? 0);
    $new_parent = intval($_POST['parent'] ?? 0);
    $position = isset($_POST['position']) ? intval($_POST['position']) : null;

    if (!$id) json_err("ID inválido.");

    // pegar siblings do novo parent
    $siblings = [];
    $res = mysqli_query($conecta, "SELECT id_5 FROM $tabela WHERE parent_id_5 = ".intval($new_parent)." ORDER BY ordem_5, id_5");
    while ($r = mysqli_fetch_assoc($res)) $siblings[] = intval($r['id_5']);

    $siblings = array_values(array_filter($siblings, function($v) use ($id){ return $v !== $id; }));

    if ($position === null || $position > count($siblings)) $position = count($siblings);

    array_splice($siblings, $position, 0, [$id]);

    $i = 1;
    foreach ($siblings as $s) {
        mysqli_query($conecta, "UPDATE $tabela SET parent_id_5 = ".intval($new_parent).", ordem_5 = ".intval($i)." WHERE id_5 = ".intval($s));
        $i++;
    }

    // reindex antigo pai (se diferente)
    $res2 = mysqli_query($conecta, "SELECT parent_id_5 FROM $tabela WHERE id_5 = ".intval($id));
    if ($res2 && ($row = mysqli_fetch_assoc($res2))) {
        $old_parent = intval($row['parent_id_5']);
        if ($old_parent !== $new_parent) {
            $j = 1;
            $r3 = mysqli_query($conecta, "SELECT id_5 FROM $tabela WHERE parent_id_5 = ".intval($old_parent)." ORDER BY ordem_5, id_5");
            while ($rr = mysqli_fetch_assoc($r3)) {
                mysqli_query($conecta, "UPDATE $tabela SET ordem_5 = $j WHERE id_5 = ".intval($rr['id_5']));
                $j++;
            }
        }
    }

    json_ok();
}

json_err("Ação desconhecida.");
