<?php
include("./conexao/conecta.php");
include 'funcoes.php';
session_start();

$id_usuario = $_SESSION['id'] ?? '0';

extract($_POST);

$dir = "imagens/uploads/";
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

/* Remove o header do base64 (qualquer tipo) */
if (preg_match('/^data:(image\/[a-zA-Z0-9]+);base64,/', $img, $m)) {
    $mime = $m[1]; // ex: image/png, image/jpeg
    $img = substr($img, strlen($m[0]));
} else {
    $resp['status'] = 'failed';
    echo json_encode($resp);
    exit;
}

$img = str_replace(' ', '+', $img);
$img_bin = base64_decode($img);

/* Detecta extensão real */
$ext_map = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/bmp' => 'bmp',
    'image/webp' => 'webp',
];

$ext = $ext_map[$mime] ?? 'bin';

/* Cria nome final */
$timestamp = time();
$data = date('d/m/Y', $timestamp);
$hora = date('H:i:s', $timestamp);

$nome_final = $timestamp . '.' . $ext;

/* Salva arquivo */
$save = file_put_contents($dir . $nome_final, $img_bin);

if ($save) {
    $sql = "INSERT INTO imagens_2 (id_usuario_2, imagem_2, data_2, hora_2)
            VALUES ('$id_usuario','$nome_final','$data','$hora')";

    if (mysqli_query($conecta, $sql)) {
        $resp['status'] = 'success';
    } else {
        $resp['status'] = 'failed';
    }
} else {
    $resp['status'] = 'failed';
}

echo json_encode($resp);
