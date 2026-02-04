<?php
include 'topo.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery.fancytree/dist/skin-win8/ui.fancytree.min.css">
<link rel="stylesheet" href="<?php echo $dominio ?>/app/plugins/css/jquery.contextMenu.min.css">
<link href="<?php echo $dominio ?>/app/css/editor_menu.css" rel="stylesheet">

<div class="card rounded-5 shadow-lg " data-pg-collapsed>
            <div class="card-body">
               <div class="container p-5">
                 <div class="mb-3">
                <label for="nivel">Nível</label>
                <select id="nivel" name="nivel" class="form-select" aria-label="Nível do usuário">
                    <?php
                    $query_11 = "SELECT * FROM niveis_11 ORDER BY id_11";
                    $sql_11 = mysqli_query($conecta, $query_11);
                    while ($linha_11 = mysqli_fetch_assoc($sql_11)) {
                        $nivel_11 = $linha_11['nivel_11']; ?>
                        <option value="<?php echo $nivel_11; ?>"><?php echo ucfirst($nivel_11); ?></option>
                    <?php
                    }
                    ?>

                </select>
            </div>
                    <div id="tree"></div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class=" text-center w-100">
                            <button id="btnRefresh" class="btn btn-sm btn-primary m-3">Recarregar</button>
                            <button id="btnAddRoot" class="btn btn-sm btn-success m-3">Novo (raiz)</button>
                        </div>
                    </div>
                    <div class="text-muted text-center">
                        Dica: arraste para mover; clique com o botão direito para opções; duplo-clique ou F2 para renomear.
                    </div>
                </div>
            </div>
        </div>
        

<!-- Modal Seletor de Ícones -->
<div class="modal fade" id="iconModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Selecionar Ícone</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="iconSearch" class="form-control" placeholder="Buscar ícone...">
                <div class="icon-grid" id="iconGrid"></div>
            </div>
            <div class="modal-footer d-flex justify-content-between align-items-center">
                <nav aria-label="Paginação de ícones">
                    <ul class="pagination pagination-sm mb-0" id="iconPagination">
                        <li class="page-item" id="pagePrev"><a class="page-link" href="#" aria-label="Anterior"><span aria-hidden="true">&laquo;</span></a></li>
                        <li class="page-item" id="pageNext"><a class="page-link" href="#" aria-label="Próxima"><span aria-hidden="true">&raquo;</span></a></li>
                    </ul>
                </nav>
                <div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btnRemoveIcon">Remover Ícone</button>
                    <button type="button" class="btn btn-primary" id="btnSelectIcon">Selecionar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo $dominio ?>/app/plugins/js/jquery-3.7.1.min.js"></script>
<script src="<?php echo $dominio ?>/app/plugins/js/jquery-ui.min.js"></script>
<script src="<?php echo $dominio ?>/app/plugins/js/jquery.fancytree-all-deps.min.js"></script>
<script src="<?php echo $dominio ?>/app/plugins/js/jquery.contextMenu.min.js"></script>
<script src="<?php echo $dominio ?>/app/js/super/icones_menu.js"></script>
<script src="<?php echo $dominio ?>/app/js/super/tree_menu.js"></script>
<?php
include 'rodape.php';
?>