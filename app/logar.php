<?php
date_default_timezone_set('America/Sao_Paulo');
include("./conexao/conecta.php");
session_start();
$email = $_POST['email'];
$senha = $_POST['senha'];
$meuip = $_POST['meuip'];
 $data = date('Y-m-d');
 $hora = date("H:i:s");
 echo "The time is " . date("h:i:sa");
if (isset($email) AND isset($senha)) {
    $query_1 = "SELECT * FROM usuarios_1 "
            . "WHERE email_1 = '$email'";
    $sql_1 = mysqli_query($conecta, $query_1);
    $cont_1 = mysqli_num_rows($sql_1);
    while ($linha_1 = mysqli_fetch_assoc($sql_1)) {
        $id_1 = $linha_1['id_1'];
        $senhaVer = $linha_1['senha_1'];
        $nivel_1 = $linha_1['nivel_1'];
        $situacao_1 = $linha_1['situacao_1'];
    }


    if ($cont_1 == 0) {
           $situacao = 'Tentou logar com o e-mail errado: ("'.$email.'")';
        $sql = "INSERT INTO controle_login_3 (id_usuario_3, ip_local_3, data_3, hora_3, situacao_3) VALUES ('$id_1','$meuip','$data','$hora','$situacao')";
        mysqli_query($conecta, $sql);
        echo "<META HTTP-EQUIV=REFRESH CONTENT='0; URL=/app/entrar/1'>";
        exit;
    }

    if ($senhaVer == '') {
        $situacao = 'Tentou logar com a senha errada: ("'.$senha.'")';
        $sql = "INSERT INTO controle_login_3 (id_usuario_3, ip_local_3, data_3, hora_3, situacao_3) VALUES ('$id_1','$meuip','$data','$hora','$situacao')";
        mysqli_query($conecta, $sql);
        echo "<META HTTP-EQUIV=REFRESH CONTENT='0; URL=/app/entrar/2'>";
        exit;
    }
    
     if ($situacao_1 == 'Bloqueado') {
         $situacao = 'Tentou logar com a conta bloqueada: ("'.$email.'")';
        $sql = "INSERT INTO controle_login_3 (id_usuario_3, ip_local_3, data_3, hora_3, situacao_3) VALUES ('$id_1','$meuip','$data','$hora','$situacao')";
        mysqli_query($conecta, $sql);
        echo "<META HTTP-EQUIV=REFRESH CONTENT='0; URL=/app/entrar/3'>";
        exit;
    }

    if (!password_verify($senha, $senhaVer)) {//confere senha  
        unset($_SESSION["id"]);
        unset($_SESSION["senha"]);
        unset($_SESSION['nivel']);
        echo "<META HTTP-EQUIV=REFRESH CONTENT='0; URL=/app/entrar/4'>";
        exit;
    }

    if (password_verify($senha, $senhaVer)) {//confere senha  
        $situacao = 'Logou com sucesso';
        $sql = "INSERT INTO controle_login_3 (id_usuario_3, ip_local_3, data_3, hora_3, situacao_3) VALUES ('$id_1','$meuip','$data','$hora','$situacao')";
        if (mysqli_query($conecta, $sql)) {
            $_SESSION["id"] = $id_1;
            $_SESSION["senha"] = $senha;
            $_SESSION['nivel'] = $nivel_1;
            echo "<META HTTP-EQUIV=REFRESH CONTENT='0; URL=/app/$nivel_1/'>";
            exit;
        } else {
            
             echo "<META HTTP-EQUIV=REFRESH CONTENT='0; URL=/app/entrar/5'>";
        exit;
        }  
    }
} else {
    echo "<META HTTP-EQUIV=REFRESH CONTENT='0; URL=/app/entrar/>";
    exit;
}
?>