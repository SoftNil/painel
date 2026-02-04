</div>
   </div>
<footer class="d-flex flex-wrap justify-content-between align-items-center border-top fixed-bottom bg-body-tertiary">
    <a href="/" class="col-md-4 d-flex align-items-center justify-content-center mb-3 mb-md-0 me-md-auto link-dark text-decoration-none">
        <img src="<?php echo imgExiste($dominio, '/app/imagens/uploads/', $logo_4) ?>" alt="" height="60" >  
    </a>
    <p class="col-md-4 mb-0 text-muted d-flex align-items-center justify-content-center">
        © <?php echo date('Y'); ?> <?php echo $titulo_4; ?>, Inc
    </p>
    
    <ul class="nav col-md-4 d-flex align-items-center justify-content-center ">
        <li class="ms-3">
            <a class="text-muted text-decoration-none" href="#"> 
            <i class="ri-twitter-x-line ri-2x"></i> </a>
        </li>
        <li class="ms-3">
            <a class="text-muted text-decoration-none" href="#"> 
            <i class="ri-instagram-fill ri-2x"></i> </a>
        </li>
        <li class="ms-3">
            <a class="text-muted text-decoration-none" href="#"> 
            <i class="ri-facebook-circle-fill ri-2x"></i></a>
        </li>
    </ul>
</footer>

<script src="<?php echo $dominio ?>/app/js/funcoes.js"></script>
<script src="<?php echo $dominio ?>/app/plugins/bootstrap5/assets/js/popper.min.js"></script>
<script src="<?php echo $dominio ?>/app/plugins/bootstrap5/bootstrap/js/bootstrap.min.js"></script>
<script src="<?php echo $dominio ?>/app/js/theme.js"></script>
</body>
</html>
