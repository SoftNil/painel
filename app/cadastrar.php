<?php
header('Content-Type: text/html; charset=utf-8');
include 'topo.php';
require 'email.php';

if (isset($_POST["nome"]) AND isset($_POST['email'])) {
    $nome = $_POST["nome"];
    $email = $_POST['email'];
    $email2 = $_POST['email2'];

    if ($email != $email2) {
        $alerta = 'alert-danger';
        $menssagem = 'Os e-mails são diferentes, por favor verifique.';
    }
    if ($email == $email2) {
        $query_1 = "SELECT * FROM usuarios_1 "
                . "WHERE email_1 = '$email'";
        $sql_1 = mysqli_query($conecta, $query_1);
        $cont_1 = mysqli_num_rows($sql_1);
        if ($cont_1 != 0) {
            $alerta = 'alert-danger';
            $menssagem = 'E-Mail já cadastrado!';
        }
        if ($cont_1 == 0) {
            $senha = gerar_senha(10, true, true, true, true);
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $data = date('Y-m-d');
            $sobre = 'Cadastro do site ' . $titulo_4;
            include 'envio_cadastro.php';
            
            $sql = "INSERT INTO usuarios_1 (nome_1, email_1, senha_1, nivel_1, situacao_1, data_cadastro_1) VALUES ('$nome','$email','$hash','usuarios','ativo','$data')";

            if (mysqli_query($conecta, $sql)) {
                $retorno = EnviarEmail($nome, $email, $sobre, $msn_html, $msn);
                if ($retorno == 'sucesso') {
                    $alerta = 'alert-success';
                    $menssagem = 'Cadastro realizado com sucesso!<br>Verifique seu email para validar seu cadastro.';
                }
                if ($retorno != 'sucesso') {
                    $alerta = 'alert-danger';
                    $menssagem = $retorno;
                }
            } else {
                $alerta = 'alert-danger';
                $menssagem = 'Não foi possível fazer o cadastro. Por favor tente mais tarde !';
            }
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
                <h2 class="fw-bold mb-0">Cadastrar</h2>
            </div>
            <div class="modal-body p-5 pt-0">
<?php if ($menssagem != '') { ?>
                    <div class="alert <?php echo $alerta; ?> alert-dismissible fade show text-center" role="alert" data-pg-collapsed><?php echo $menssagem; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
<?php } ?>
                <form class="" method="post" autocomplete="on" action="">
                    <input type="hidden" id="msn_html" name="msn_html" value="Email enviado com HTML">
                    <input type="hidden" id="msn" name="msn" value="Email enviado sem HTML">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control rounded-4" id="nome" name="nome" value="<?php echo $nome; ?>" placeholder="Digite seu Nome" required="true">
                        <label for="floatingPassword">Digite seu Nome</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control rounded-4" id="email" name="email" value="<?php echo $email; ?>" placeholder="name@example.com" required="true">
                        <label for="floatingInput">Digite seu E-Mail</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control rounded-4" id="email2" name="email2" value="<?php echo $email2; ?>" placeholder="name@example.com" required="true">
                        <label for="floatingInput">Digite novamente seu E-Mail</label>
                    </div>
                    <hr class="my-4">
                    <button class="w-100 mb-2 btn btn-lg rounded-4 btn-primary" type="submit">Cadastrar-se</button>
                    <small class="text-muted">Ao clicar em Cadastrar-se, você concorda com os <a href="<?php echo $dominio . '/app/termos'; ?>" target="_blank">termos</a> de uso.</small>
                    <!--<h2 class="fs-5 fw-bold mb-3">Ou use um terceiro</h2>
                    <button class="w-100 py-2 mb-2 btn btn-outline-dark rounded-4" type="submit"><i class="ri-twitter-fill"></i> Inscreva-se com Twitter</button>
                    <button class="w-100 py-2 mb-2 btn btn-outline-primary rounded-4" type="submit"><i class="ri-facebook-circle-fill"></i> Inscreva-se com Facebook</button>
                    <button class="w-100 py-2 mb-2 btn btn-outline-secondary rounded-4" type="submit"><i class="ri-google-fill"></i> Inscreva-se com Google</button>-->
                    <a type="button" class="w-100 mb-2 btn rounded-4 btn-link" href="<?php echo $dominio . '/app/entrar'; ?>"><i class="ri-restart-line"></i> Retornar</a>
                </form>
            </div>
        </div>     
    </div>
</div>
<?php
include 'rodape.php';
?>
