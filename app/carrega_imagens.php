<?php
session_start();
include './conexao/conecta.php';

include 'funcoes.php';

 $pagina = max(1, intval($_GET['p'] ?? 1));
$registros = 6;

$id_usuario = $_SESSION['id'];
$inicio = ($pagina - 1) * $registros;

// ==========================
// QUERY COM LIMIT (LISTAGEM)
// ==========================
if ($_SESSION['nivel'] != 'admin') {
    $query_2 = "SELECT * FROM imagens_2 
                WHERE id_usuario_2 = '$id_usuario' 
                ORDER BY id_2 DESC
                LIMIT $inicio, $registros";
} else {
    $query_2 = "SELECT * FROM imagens_2 
                ORDER BY id_2 DESC
                LIMIT $inicio, $registros";
}

$sql_2 = mysqli_query($conecta, $query_2);

// ==========================
// QUERY SEM LIMIT (CONTAGEM)
// ==========================
if ($_SESSION['nivel'] != 'admin') {
    $queryCount = "SELECT COUNT(*) AS total 
                   FROM imagens_2 
                   WHERE id_usuario_2 = '$id_usuario'";
} else {
    $queryCount = "SELECT COUNT(*) AS total FROM imagens_2";
}

$resCount = mysqli_query($conecta, $queryCount);
$rowCount = mysqli_fetch_assoc($resCount);

$total = $rowCount['total'];
$qtdPag = ceil($total / $registros);

?>

<div class="row imagems pb-5 pe-5 ps-5">
    <div class="col-lg-12">
        <div class="d-flex flex-wrap">
            <?php
            $dir =  '/app/imagens/uploads/';
             /* FLAG PARA JS */
                echo '<div data-ultima="' . $qtdPag  . '" data-pagina="' . $pagina . '" id="metaPag"></div>';
            while ($linha_2 = mysqli_fetch_assoc($sql_2)) {
                $id_2 = $linha_2['id_2'];
                $imagem_2 = $linha_2['imagem_2'];
                $data_2 = $linha_2['data_2'];
                $hora_2 = $linha_2['hora_2'];
                
            ?>
                <div class="col-lg-4 col-md-6 col-sm-12 p-1">
                    <div class="card mb-5" data-pg-collapsed>
                        <div class="position-relative img-holder">                          
                            <img src="<?php echo imgExiste($dominio, $dir, $imagem_2) ?>" class="img-fluid rounded border w-100" style="cursor:pointer" onclick="selecionarImagem('<?php echo $imagem_2; ?>')">                      
                    <button class="btn btn-sm btn-outline-danger btn-deletar rounded-5" data-id="<?= $linha_2['id_2'] ?>" data-img="<?= $linha_2['imagem_2'] ?>" title="Excluir"><i class="ri-close-large-fill ri-1x"></i></button>
                    </div>
                        <div class="card-body text-center">
                            <p class="card-text"><i class="ri-calendar-event-line"></i> Data: <?php echo $data_2 ?> <i class="ri-time-line"></i> Hora: <?php echo $hora_2 ?></p>
                            <form action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">
                                <input type="hidden" id="id" name="id" value="<?php echo $id_2; ?>">
                                <input type="hidden" id="imagem" name="imagem" value="<?php echo $imagem_2; ?>">
                                
                            </form>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>