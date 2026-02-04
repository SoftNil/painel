<?php
// Charset ISO-8859-1
//header('Content-Type: text/html; charset=ISO-8859-1');

// Detalhes da conexão
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $protocol = "https";
} else {
    $protocol = "http";
}
$URL_ATUAL= "$_SERVER[HTTP_HOST]";

if ($URL_ATUAL == 'painel.local'){
$servidor = 'localhost';
$usuario  = 'root';
$senha    = '';
$bd       = 'app';
$prefixo  = '';
$charset  = 'UTF8';
}else
if ($URL_ATUAL == 'localhost:8080'){
$servidor = 'localhost';
$usuario  = 'root';
$senha    = '';
$bd       = 'app';
$prefixo  = '';
$charset  = 'UTF8';
}else{
$servidor = 'painel.softnil.com.br';
$usuario  = 'c1softnil';
$senha    = 'Del123!@#';
$bd       = 'c1softnil';
$prefixo  = '';
$charset  = 'UTF8';	    
}
$dominio = $protocol.'://'.$URL_ATUAL;
?>	
