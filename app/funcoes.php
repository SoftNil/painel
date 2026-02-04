<?php
function LinkExterno($url)
{
    $data = explode("//", $url);
    if ($data[0] == "https:" or $data[0] == "http:") {
        return true;
    } else {
        return false;
    }
}

function PegaLink($num)
{
    $data = explode("/", $_SERVER["REQUEST_URI"]);
    return $data[$num];
}

function criptografa($texto)
{
    return  base64_encode($texto);
}


function descriptografa($texto)
{
    return base64_decode($texto);
}


function Logado()
{
    session_start();
    if (isset($_SESSION["id"]) and isset($_SESSION['senha']) and isset($_SESSION['nivel'])) {
        return true;
    } else {
        return false;
    }
}

function gerar_senha($tamanho, $maiusculas, $minusculas, $numeros, $simbolos)
{
    $ma = "ABCDEFGHIJKLMNOPQRSTUVYXWZ"; // $ma contem as letras maiúsculas
    $mi = "abcdefghijklmnopqrstuvyxwz"; // $mi contem as letras minusculas
    $nu = "0123456789"; // $nu contem os números
    $si = "!@#$%¨&*()_+="; // $si contem os símbolos
    $senha = '';
    if ($maiusculas) {
        // se $maiusculas for "true", a variável $ma é embaralhada e adicionada para a variável $senha
        $senha .= str_shuffle($ma);
    }

    if ($minusculas) {
        // se $minusculas for "true", a variável $mi é embaralhada e adicionada para a variável $senha
        $senha .= str_shuffle($mi);
    }

    if ($numeros) {
        // se $numeros for "true", a variável $nu é embaralhada e adicionada para a variável $senha
        $senha .= str_shuffle($nu);
    }

    if ($simbolos) {
        // se $simbolos for "true", a variável $si é embaralhada e adicionada para a variável $senha
        $senha .= str_shuffle($si);
    }

    // retorna a senha embaralhada com "str_shuffle" com o tamanho definido pela variável $tamanho
    return substr(str_shuffle($senha), 0, $tamanho);
}

function get_client_ip()
{
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if (isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

function soNumero($str)
{
    return preg_replace("/[^0-9]/", "", $str);
}

function PontoPorVirgula($str)
{
    return str_replace(".", ",", $str);
}

function VirgulaPorPonto($str)
{
    return str_replace(",", ".", $str);
}

function separa($sinbolo, $str, $num)
{
    $string = explode($sinbolo, $str);
    return $string[$num];
}

function formatoReal($valor)
{
    $valor = (string)$valor;
    $regra = "/^[0-9]{1,3}([.]([0-9]{3}))*[,]([.]{0})[0-9]{0,2}$/";
    if (preg_match($regra, $valor)) {
        return true;
    } else {
        return false;
    }
}

function moeda($get_valor)
{
    $source = array('.', ',');
    $replace = array('', '.');
    $valor = str_replace($source, $replace, $get_valor); //remove os pontos e substitui a virgula pelo ponto
    return $valor; //retorna o valor formatado para gravar no banco
}

function dataPBanco($datainv)
{
    $ano = substr("$datainv", 6); //04/02/1976
    $mes = substr("$datainv", 3, -5); //04/02/1976
    $dia = substr("$datainv", 0, -8); //04/02/1976
    $datainv = "$ano-$mes-$dia";
    return $datainv;
}

function dataPBancoTime($dataIni)
{
    $hora = substr($dataIni, 11);
    $dataIni = substr($dataIni, 0, 10);
    $dataIni = dataPBanco($dataIni);
    $dataIni = $dataIni . " " . $hora;
    return $dataIni;
}


function dataPForaTime($data_ini)
{
    $hora =  substr($data_ini, 10);
    $data_ini = substr($data_ini, 0, 10);
    $data_ini = dataPFora($data_ini);
    $data_ini = $data_ini . "" . $hora;
    return $data_ini;
}

function dataPFora($datainv)
{
    $ano = substr("$datainv", 0, -6); //1976-02-04
    $mes = substr("$datainv", 5, -3); //1976-02-04
    $dia = substr("$datainv", 8); //1976-02-04
    $datainv = "$dia/$mes/$ano";
    return $datainv;
}

function valForaPBanco($valor)
{
    $novoForm = substr("$valor", 3);
    $result = floatval($novoForm);
    echo $result;
    die();
    return $novoForm;
}

function geraTimestamp($data)
{
    $partes = explode('/', $data);
    return mktime(0, 0, 0, $partes[1], $partes[0], $partes[2]);
}

function diferencaEntreDatas($data_inicial, $data_final)
{
    $time_inicial = geraTimestamp($data_inicial);
    $time_final = geraTimestamp($data_final);
    $diferenca = $time_final - $time_inicial;
    $dias = (int)floor($diferenca / (60 * 60 * 24));
    return $dias;
}

function LigadoDesligado($tipo)
{
    if ($tipo == 1) {
        return '<i class="ri-checkbox-line"></i>';
    } else {
        return '<i class="ri-checkbox-blank-line"></i>';
    }
}


/* ============================
   FUNÇÃO RECURSIVA DO MENU
============================= */
function montarMenu($parent, $menus, $dominio)
{
    if (!isset($menus[$parent])) return;

    echo '<ul class="dropdown-menu dropend">';

    foreach ($menus[$parent] as $item) {
        $hasChildren = isset($menus[$item['id_5']]);
        $id_5 = $item['id_5'];
        $icone_5 = $item['icone_5'];
        $texto_5 = $item['texto_5'];
        $link_5 = $item['link_5'];
        if (LinkExterno($link_5) == true) {
            $target = 'target="_blank"';
        }
        if (LinkExterno($link_5) == false) {
            $target = '';
            $link_5 = $dominio . '/app/admin/' . $link_5;
        }
        if ($hasChildren) {
            echo '<li class="dropdown-submenu">';
            echo '<a class="dropdown-item dropdown-toggle fw-bold" ' . $target . ' href="' . $link_5 . '">' . $icone_5 . ' ' . $texto_5 . '</a>';
            montarMenu($id_5, $menus, $dominio);
            echo '</li>';
        } else {
            echo '<li><a class="dropdown-item fw-bold" ' . $target . ' href="' . $link_5 . '">' . $icone_5 . ' ' . $texto_5 . '</a></li>';
        }
    }

    echo '</ul>';
}

function imgExiste($dominio, $pasta, $arquivo)
{
    // imagem padrão
    $padrao = $dominio . '/app/imagens/site/sem_imagem.png';

    if (!$arquivo) {
        return $padrao;
    }

    $path = $_SERVER['DOCUMENT_ROOT'] . $pasta . $arquivo;

    if (file_exists($path) && is_file($path)) {
        return $dominio . $pasta . $arquivo;
    }

    // SE NÃO EXISTIR → retorna imagem padrão
    return $padrao;
}




