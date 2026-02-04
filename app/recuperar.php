<?php
header('Content-Type: text/html; charset=utf-8');
include 'topo.php';
require 'email.php';

if (isset($_POST['email'])) {
    $email = $_POST['email'];

    $query_1 = "SELECT * FROM usuarios_1 "
            . "WHERE email_1 = '$email'";
    $sql_1 = mysqli_query($conecta, $query_1);
    $cont_1 = mysqli_num_rows($sql_1);
    while ($linha_1 = mysqli_fetch_assoc($sql_1)) {
        $nome = $linha_1['nome_1']; 
    }
    if ($cont_1 == 0) {
        $alerta = 'alert-danger';
        $menssagem = 'E-Mail não encontrado!';
    }
    if ($cont_1 != 0) {
        $codigo = gerar_senha(10, true, false, true, false);
        $codigo_cripitografado = criptografa($codigo);
        $sobre = 'Recuperação da senha do site ' . $titulo_4;
        include 'envio_codico.php';
        $sql = "UPDATE usuarios_1 SET codigo_1='$codigo_cripitografado' WHERE email_1 = '$email'";

        if (mysqli_query($conecta, $sql)) {
            $retorno = EnviarEmail($nome, $email, $sobre, $msn_html, $msn);
            if ($retorno == 'sucesso') {
                $alerta = 'alert-success';
                $menssagem = 'Recuperação realizado com sucesso!<br>Verifique seu email para validar seu código de recuperação.';
            }
            if ($retorno != 'sucesso') {
                $alerta = 'alert-danger';
                $menssagem = $retorno;
            }
        } else {
            $alerta = 'alert-danger';
            $menssagem = 'Não foi possível fazer a recuperação. Por favor tente mais tarde !';
        }
    }
}
?>    
<div class="modal modal-signin position-static d-block py-5" tabindex="-1" role="dialog" id="modalSignin" data-pg-collapsed> 
    <div class="modal-dialog" role="document">
        <div class="modal-content rounded-5 shadow">
            <div class="col-md-12 text-center pt-5" data-pg-collapsed>
                <img src="<?php echo imgExiste($dominio, '/app/imagens/uploads/', $logo_4) ?>" height="100"  class="rounded">
            </div>
            <div class="modal-header p-5 pb-4 border-bottom-0">
                <!-- <h5 class="modal-title">Modal title</h5> -->
                <h2 class="fw-bold mb-0">Esqueci a senha</h2>
            </div>
            <div class="modal-body p-5 pt-0">
                <?php if ($menssagem != '') { ?>
                    <div class="alert <?php echo $alerta; ?> alert-dismissible fade show text-center" role="alert" data-pg-collapsed><?php echo $menssagem; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
<?php } ?>
                <form class="" method="post" autocomplete="on" action="">
                    <div class="form-floating mb-3">
                         <input type="email" class="form-control rounded-4" id="email" name="email" value="<?php echo $email; ?>" placeholder="name@example.com" required="true">
                        <label for="floatingInput">Digite seu E-Mail</label>
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