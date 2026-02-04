  <?php
  include 'topo.php';

  if (isset($_POST["id"]) and isset($_POST['imagem'])) {
  ?>
    <div class="position-relative img-holder">
      <img src="imagens/uploads/<?php echo $_POST['imagem']; ?>" class="w-100" />
    </div>
  <?php
  }
$pagina = PegaLink(2);

  ?>
  <form action="imagem" method="POST" enctype="multipart/form-data">
    <input type="text" id="link" name="link" value="<?php echo $pagina; ?>">
    <button type="submit" class="btn btn-danger">Imagem</button> 
  </form>

  <?php
  include 'rodape.php';
  ?>