<?php

include("./conexao/conecta.php");
session_start();

if (isset($_SESSION["id"]) AND isset($_SESSION['senha']) AND isset($_SESSION['nivel'])) {
    $id = $_SESSION["id"];
    $senha = $_SESSION["senha"];
    $nivel = $_SESSION["nivel"];

    $query_1 = "SELECT * FROM usuarios_1 "
             . "WHERE id_1 = '$id'";
    $sql_1 = mysqli_query($conecta, $query_1);
    $cont_1 = mysqli_num_rows($sql_1);
    while ($linha_1 = mysqli_fetch_assoc($sql_1)) {
        $id_1 = $linha_1['id_1'];
        $senhaVer = $linha_1['senha_1'];
        $nivel_1 = $linha_1['nivel_1'];
    }
    if ($cont_1 == 0) {
        unset($_SESSION["id"]);
        unset($_SESSION["senha"]);
        unset($_SESSION['nivel']);

        echo "<META HTTP-EQUIV=REFRESH CONTENT='0; URL=/app/entrar'>";
        exit;
    }
    if (!password_verify($senha, $senhaVer)) {//confere senha
        unset($_SESSION['id']);
        unset($_SESSION["login"]);
        unset($_SESSION["senha"]);
        echo "<META HTTP-EQUIV=REFRESH CONTENT='0; URL=/app/entrar'>";
        exit;
    }
    if (password_verify($senha, $senhaVer)) {//confere senha  
       
          echo "<META HTTP-EQUIV=REFRESH CONTENT='0; URL=/app/$nivel'>";
        exit;
    }  
} else {
    echo "<META HTTP-EQUIV=REFRESH CONTENT='0; URL=/app/entrar/'>";
    exit;
}
?>