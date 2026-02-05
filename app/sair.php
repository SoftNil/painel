 <?php
include("./conexao/conecta.php");
session_start();
unset($_SESSION['id']);
unset($_SESSION["login"]);
unset($_SESSION["senha"]);
echo "<META HTTP-EQUIV=REFRESH CONTENT='0; URL=entrar'>";
?>