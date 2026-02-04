<?php
header('Content-Type: text/html; charset=utf-8');
include 'topo.php';
require 'email.php';

if (isset($_POST['email'])) {
    $email = $_POST['email'];


    $retorno = EnviarEmail($nome, $email, $sobre, $msn_html, $msn);
    if ($retorno == 'sucesso') {
        $alerta = 'alert-success';
        $menssagem = 'Recuperação realizado com sucesso!<br>Verifique seu email para validar seu código de recuperação.';
    }
    if ($retorno != 'sucesso') {
        $alerta = 'alert-danger';
        $menssagem = $retorno;
    }
}
?>
<div class="modal position-static d-block">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-5 shadow p-5">
            <div class="modal-header border-bottom-0">
                <h1 class="fw-bold mb-0 text-center w-100">Imagens</h1>
            </div>
            <div class="col-lg-12 text-center">
                <i class="ri-message-2-fill ri-6x"></i>
                <div>
                    <div class="container contato">
                        <div class="row justify-content-center">
                            <form id="form_cadastrar" method="post" enctype="multipart/form-data">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <div class="input-group mb-3" data-pg-collapsed><span class="input-group-text">Nome:</span>
                                            <input type="text" class="form-control" id="nome" name="nome" placeholder="Digite seu nome aqui." required>
                                            <span class="input-group-text"><i class="ri-user-fill"></i></span>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="input-group mb-3" data-pg-collapsed><span class="input-group-text">E-mail:</span>
                                            <input type="email" class="form-control" id="email" name="email" placeholder="Digite seu e-mail aqui."
                                                data-error="Por favor, informe um e-mail correto." required>
                                            <span class="input-group-text"><i class="ri-mail-fill"></i></span>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="input-group mb-3" data-pg-collapsed><span class="input-group-text">Celular:</span>
                                            <input type="tel" class="form-control celular" id="celular" name="celular" placeholder="Digite seu celular aqui."
                                                data-error="Por favor, informe um celular correto." required>
                                            <span class="input-group-text"><i class="ri-smartphone-fill"></i></span>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="input-group mb-3" data-pg-collapsed><span class="input-group-text">Motivo:</span>
                                            <select class="form-select" id="motivo" name="motivo" aria-label="Example select with button addon" required>
                                                <option selected>Escolher...</option>
                                                <option value="Duvidas em comprar">Duvidas em comprar</option>
                                                <option value="Devolução de produtos">Devolução de produtos</option>
                                                <option value="Problemas para comprar">Problemas para comprar</option>
                                                <option value="Problemas na entrega">Problemas na entrega</option>
                                                <option value="Parcerias para divulgação">Parcerias para divulgação</option>
                                                <option value="Outros">Outros</option>
                                            </select>
                                            <span class="input-group-text"><i class="ri-chat-1-fill"></i></span>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label for="your-message" class="form-label">Sua Mensagem</label>
                                        <textarea class="form-control" rows="5" id="assunto" name="assunto" placeholder="Digite informações complementares." required></textarea>
                                    </div>
                                    <div id="alerta"></div>
                                    <div class="col-4">
                                    </div>
                                    <div class="col-4" id="btn_enviar">
                                        <button type="button" onclick="contato();" class="btn btn-dark w-100 fw-bold">Enviar</button>
                                    </div>
                                    <div class="col-4">
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$fixed  = 'fixed-bottom';
include 'rodape.php';
?>