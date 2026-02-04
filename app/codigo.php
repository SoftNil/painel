<?php
header('Content-Type: text/html; charset=utf-8');
include 'topo.php';
require 'email.php';

if (isset($_POST['codigo'])) {
    $codigo = $_POST['codigo'];
   $codigo_cripitografado = criptografa($codigo);
   echo $codigo_decripitografado;
    $query_1 = "SELECT * FROM usuarios_1 "
            . "WHERE codigo_1 = '$codigo_cripitografado'";
    $sql_1 = mysqli_query($conecta, $query_1);
    $cont_1 = mysqli_num_rows($sql_1);
    while ($linha_1 = mysqli_fetch_assoc($sql_1)) {
        $nome = $linha_1['nome_1']; 
        $email = $linha_1['email_1'];  
    }
    if ($cont_1 == 0) {
        $alerta = 'alert-danger';
        $menssagem = 'O código é inválido!';
    }
    if ($cont_1 != 0) {
        $senha = gerar_senha(10, true, true, true, true);
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $codigo_cripitografado = criptografa($codigo);
        $sobre = 'Recuperação da senha do site ' . $titulo_4;
        include 'envio_recuperar.php';
       
        $sql = "UPDATE usuarios_1 SET senha_1='$hash', codigo_1='' WHERE email_1 = '$email'";

        if (mysqli_query($conecta, $sql)) {
            $retorno = EnviarEmail($nome, $email, $sobre, $msn_html, $msn);
            if ($retorno == 'sucesso') {
                $alerta = 'alert-success';
                $menssagem = 'Código válidado com sucesso!<br>Verifique seu email foi enviado uma nova senha.';
            }
            if ($retorno != 'sucesso') {
                $alerta = 'alert-danger';
                $menssagem = $retorno;
            }
        } else {
            $alerta = 'alert-danger';
            $menssagem = 'Não foi possível validar o código. Por favor tente mais tarde !';
        }
    }
}
?>    
<div class="modal modal-signin position-static d-block py-5" tabindex="-1" role="dialog" id="modalSignin" data-pg-collapsed> 
    <div class="modal-dialog" role="document">
        <div class="modal-content rounded-5 shadow">
            <div class="col-md-12 text-center pt-5" data-pg-collapsed>
                <img src="<?php echo $dominio . '/app/imagens/site/' . $logo_4 ?>" height="100"  class="rounded">
            </div>
            <div class="modal-header p-5 pb-4 border-bottom-0">
                <!-- <h5 class="modal-title">Modal title</h5> -->
                <h2 class="fw-bold mb-0">Recuperar senha</h2>
            </div>
            <div class="modal-body p-5 pt-0">
                <?php if ($menssagem != '') { ?>
                    <div class="alert <?php echo $alerta; ?> alert-dismissible fade show text-center" role="alert" data-pg-collapsed><?php echo $menssagem; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
<?php } ?>
                <form class="" method="post" autocomplete="on" action="">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control rounded-4" id="codigo" name="codigo" value="<?php echo $codigo; ?>"  required="true">
                        <label for="floatingInput">Digite o codigo enviado para seu E-Mail</label>
                    </div> 
                    <hr class="my-4">
                    <button class="w-100 mb-2 btn btn-lg rounded-4 btn-primary" type="submit">Recuperar</button>
                    <a type="button" class="w-100 mb-2 btn rounded-4 btn-link" href="<?php echo $dominio . '/app/entrar'; ?>"><i class="ri-restart-line"></i> Retornar</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
include 'rodape.php';
?>