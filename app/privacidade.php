<?php
$senha = "admin";
$hash = password_hash($senha, PASSWORD_DEFAULT);
//echo $hash;
include 'topo.php';
$menssagem ='';
if (PegaLink(3) == 1){
 $menssagem = 'Usuário não cadastrado!';   
}
if (PegaLink(3) == 2){
 $menssagem = 'O e-mail do usuário ainda não foi validado!';   
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
                <h2 class="fw-bold mb-0">Entrar</h2>
            </div>
            <div class="modal-body p-5 pt-0">
                <?php if ($menssagem != ''){ ?>
                <div class="alert alert-danger alert-dismissible fade show text-center" role="alert" data-pg-collapsed><?php echo $menssagem; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php } ?>
                <form class="" method="post" autocomplete="on" action="<?php echo $dominio ?>/app/logar">
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control rounded-4" id="email" name="email" placeholder="name@example.com" required="true">
                        <label for="floatingInput">Digite seu E-Mail</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control rounded-4" id="senha" name="senha" placeholder="Senha" required="true">
                        <label for="floatingPassword">Digite sua senha</label>
                    </div>
                    <hr class="my-4">
                    <div class="row">
                        <div class="col-md-4" data-pg-collapsed>
                            <button class="w-100 mb-2 btn rounded-4 btn-primary" type="submit"><i class="ri-check-line"></i> Entrar</button>
                        </div>
                        <div class="col-md-4" data-pg-collapsed>
                            <a class="w-100 py-2 mb-2 btn btn-success rounded-4" href="<?php echo $dominio . '/app/cadastrar'; ?>" type="button"><i class="ri-sticky-note-add-line"></i> Cadastrar</a>
                        </div>
                        <div class="col-md-4" data-pg-collapsed>
                            <a class="w-100 py-2 mb-2 btn btn-danger rounded-4" href="<?php echo $dominio . '/app/recuperar'; ?>" type="button"><i class="ri-question-mark"></i> Esqueci</a>
                        </div>
                    </div>                                     
                </form>
            </div>
        </div>
    </div>
</div>
<?php
include 'rodape.php';
?>