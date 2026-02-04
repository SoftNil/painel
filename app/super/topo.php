<?php
session_start();
include '../conexao/conecta.php';
include '../funcoes.php';

if (Logado() ==  false) {
    include '../verifica.php';
    exit;
} else {
    if ($_SESSION['nivel'] != 'super') {
        $link = $dominio . '/app/' . $_SESSION['nivel'];
        echo "<META HTTP-EQUIV=REFRESH CONTENT='0; URL=$link'>";
    }
}

//action.php tem que estar aqui para funcionar o AJAX
include 'action.php';

$query_4 = "SELECT * FROM configuracoes_4";
$sql_4 = mysqli_query($conecta, $query_4);
while ($linha_4 = mysqli_fetch_assoc($sql_4)) {
    $titulo_4 = $linha_4['titulo_4'];
    $logo_4 = $linha_4['logo_4'];
    $descricao_4 = $linha_4['descricao_4'];
}

$sql = "SELECT * FROM menu_super_11 ORDER BY parent_id_11, ordem_11, texto_11";
$result = mysqli_query($conecta, $sql);

$menus = [];
while ($row = mysqli_fetch_assoc($result)) {
    $menus[$row['parent_id_11']][] = $row;
}
$menu_tipo = 'topo';
//$menu_tipo = 'lateral simples';
//$menu_tipo = 'lateral completo';

?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="<?php echo $descricao_4; ?>">
    <meta name="author" content="Marcio Souza">
    <title><?php echo $titulo_4; ?></title>
    <!--CSS -->
    <link href="<?php echo $dominio ?>/app/plugins/bootstrap5/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="<?php echo $dominio ?>/app/plugins/bootstrap5/assets/js/color-modes.js"></script>
    <link href="<?php echo $dominio ?>/app/plugins/remixicon/remixicon.css" rel="stylesheet">
    <link rel="icon" href="<?php echo imgExiste($dominio, '/app/imagens/uploads/', $logo_4); ?>" />

    <!-- Custom styles for this template -->
    <link href="<?php echo $dominio ?>/app/css/admin/style.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg  border-bottom shadow fixed-top bg-body-tertiary" data-pg-collapsed>
        <div class="container-fluid me-5 ms-5 pe-5 ps-5">
            <a class="navbar-brand fs-4 fw-bold ms-5 ps-5 navbar-top" href="#"><img src="<?php echo imgExiste($dominio, '/app/imagens/uploads/', $logo_4) ?>" alt="" height="60"> <?php echo $titulo_4; ?></a>
            <?php if ($menu_tipo == 'topo') { ?>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarColor01" aria-controls="navbarColor01" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarColor01">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <?php
                        if (isset($menus[0])) {
                            foreach ($menus[0] as $item) {
                                $hasChildren = isset($menus[$item['id_11']]);
                                $id_11 = $item['id_11'];
                                $icone_11 = $item['icone_11'];
                                $texto_11 = $item['texto_11'];
                                $link_11 = $item['link_11'];
                                if ($hasChildren) {
                                    echo '<li class="nav-item dropdown">';
                                    echo '<a class="nav-link dropdown-toggle fw-bold link-body-emphasis" ' . $target . ' href="' . $link_11 . '" id="menu' . $id_11 . '" role="button" data-bs-toggle="dropdown">' . $icone_11 . ' ' . $texto_11 . '</a>';

                                    montarMenu($id_11, $menus, $dominio);

                                    echo '</li>';
                                } else {
                                    echo '<li class="nav-item"> <a class="nav-link fw-bold link-body-emphasis" ' . $target . ' href="' . $link_11 . '">' . $icone_11 . ' ' . $texto_11 . ' </a></li>';
                                }
                            }
                        }
                        ?>
                    </ul>
                    <div class="dropdown text-end">
                        <a href="#" class="d-block text-decoration-none dropdown-toggle nav-link link-body-emphasis" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                           <i class="ri-shield-user-line ri-1x"></i>
                            <strong>Administração</strong>
                        </a>
                        <?php include 'menu.php'; ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </nav>


    <div class="d-flex align-items-center justify-content-center container-centread" data-pg-collapsed>
        <?php if ($menu_tipo == 'lateral simples') { ?>
            <div class="d-flex flex-column flex-shrink-0 bg-body-tertiary shadow-lg border-end h-100">
                <ul class="nav nav-pills nav-flush flex-column mb-auto text-center">
                    <?php
                    if (isset($menus[0])) {
                        foreach ($menus[0] as $item) {
                            $hasChildren = isset($menus[$item['id_11']]);
                            $id_11 = $item['id_11'];
                            $icone_11 = $item['icone_11'];
                            $texto_11 = $item['texto_11'];
                            $link_11 = $item['link_11'];
                            if ($hasChildren) {
                                echo '<li class="nav-item dropend">';
                                echo '<a class="nav-link dropdown-toggle link-body-emphasis" ' . $target . ' href="' . $link_11 . '" id="menu' . $id_11 . '" role="button" data-bs-toggle="dropdown"><span style="font-size: 26px;" >' . $icone_11 .  '</span></a>';
                                montarMenu($id_11, $menus, $dominio);
                                echo '</li>';
                            } else {
                                echo '<li> <a class="nav-link link-body-emphasis" style="font-size: 26px;" ' . $target . ' href="' . $link_11 . '">' . $icone_11 . ' </a></li>';
                            }
                        }
                    }
                    ?>
                </ul>

                <div class="dropend">
                    <a href="#" class="d-flex align-items-center justify-content-center link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ri-shield-user-line ri-2x"></i>

                    </a>
                     <?php include 'menu.php'; ?>
                </div>
            </div>
        <?php }
        if ($menu_tipo == 'lateral completo') {
        ?>
            <div class="d-flex flex-column flex-shrink-0 p-3 bg-body-tertiary shadow-lg border-end h-100">
                <ul class="nav nav-pills flex-column mb-auto">
                    <?php

                    if (isset($menus[0])) {
                        foreach ($menus[0] as $item) {
                            $hasChildren = isset($menus[$item['id_11']]);
                            $id_11 = $item['id_11'];
                            $icone_11 = $item['icone_11'];
                            $texto_11 = $item['texto_11'];
                            $link_11 = $item['link_11'];
                            if ($hasChildren) {
                                echo '<li class="nav-item dropend">';
                                echo '<a class="nav-link dropdown-toggle fw-bold link-body-emphasis"  ' . $target . ' href="' . $link_11 . '" id="menu' . $id_11 . '" role="button" data-bs-toggle="dropdown">' . $icone_11 . ' ' . $texto_11 . '</a>';
                                montarMenu($id_11, $menus, $dominio);
                                echo '</li>';
                            } else {
                                echo '<li class="nav-item"> <a class="nav-link link-body-emphasis fw-bold" ' . $target . ' href="' . $link_11 . '">' . $icone_11 . ' ' . $texto_11 . ' </a></li>';
                            }
                        }
                    }
                    ?>
                </ul>
                <div class="dropend">
                    <a href="#" class="d-flex align-items-center link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ri-shield-user-line ri-1x me-1"></i> 
                        <strong>Administração</strong>
                    </a>
                     <?php include 'menu.php'; ?>
                </div>
            </div>
        <?php } ?>
        <div class="d-flex align-items-center justify-content-center container-conteudo" data-pg-collapsed>
            <script>
                // Habilitar submenus em Bootstrap 5
                document.querySelectorAll('.dropdown-submenu .dropdown-toggle').forEach(function(el) {
                    el.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        let submenu = this.nextElementSibling;

                        // Fecha outros submenus abertos
                        let openMenus = this.closest('.dropdown-menu').querySelectorAll('.show');
                        openMenus.forEach(function(menu) {
                            if (menu !== submenu) menu.classList.remove('show');
                        });

                        submenu.classList.toggle('show');
                    });
                });
            </script>