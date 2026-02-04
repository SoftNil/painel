<?php
include 'topo.php';
?>    
<div class="modal modal-signin position-static d-block py-5" tabindex="-1" role="dialog" id="modalSignin" data-pg-collapsed> 
    <div class="modal-dialog" role="document">
        <div class="modal-content rounded-5 shadow">
            <div class="col-md-12 text-center pt-5" data-pg-collapsed>
                <img src="<?php echo $dominio .'/app/imagens/site/'. $logo_4 ?>" height="100"  class="rounded">
            </div>
            <div class="modal-header p-5 pb-4 border-bottom-0">
                <!-- <h5 class="modal-title">Modal title</h5> -->
                <h2 class="fw-bold mb-0">Recuperar senha</h2>
            </div>
            <div class="modal-body p-5 pt-0">
                <form class="">
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control rounded-4" id="floatingInput" placeholder="name@example.com">
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