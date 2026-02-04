<?php
$rota = $_GET['pg'] ?? 'entrar';
include "./{$rota}.php";
?>