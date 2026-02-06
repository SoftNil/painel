
  <script src="<?php echo $dominio ?>/app/plugins/js/jquery-3.7.1.min.js"></script>
  <script src="<?php echo $dominio ?>/app/plugins/croppie/croppie.js"></script>

  <link rel="stylesheet" href="<?php echo $dominio ?>/app/css/imagem.css">
  <link rel="stylesheet" href="<?php echo $dominio ?>/app/plugins/croppie/croppie.css">

  <div class="modal fade" id="modalImagens" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
          <div class="modal-content shadow">

              <div class="modal-header">
                  <h2>Imagens</h2><button class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <div class="row mt-lg-5">
                  <div class="col-lg-12">
                      <form action="upload_imagem.php" method="POST" enctype="multipart/form-data">
                          <div class="col-lg-6 col-md-8 col-sm-12 mx-auto">
                              <div id="divDeleteImagem" class="d-none">
                                  <div class="modal modal-alert position-static d-block py-5" tabindex="-1" role="dialog" id="modalChoice" data-pg-collapsed>
                                      <div class="modal-dialog modal-dialog-centered" role="document">
                                          <div class="modal-content rounded-4 shadow">
                                              <div class="modal-body p-4 text-center">

                                                  <h5 class="mb-0">Deseja mesmo excluír esta imagem?</h5>
                                                  <div class="position-relative img-holder">
                                                      <img id="previewDeletar" src="" class="w-100" />
                                                  </div>
                                                  <p class="mb-0">A acão não podem desfeita. As imagens deletadas não poderão ser recuperadas.</p>
                                              </div>
                                              <div class="modal-footer flex-nowrap p-0">
                                                  <div id="btnDelete"></div>
                                                  <button type="button" class="btn btn-lg btn-link fs-6 text-decoration-none col-6 m-0 rounded-0 btn-cancelar">Cancelar</button>
                                              </div>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                              <div id="divAdd" class="">
                                  <div class="mb-5">
                                      <input class="form-control" type="file" name="upload" accept="image/jpeg, image/png, image/bmp, image/webp" id="upload">
                                  </div>
                              </div>
                          </div>
                      </form>
                      <div id="croppie-editor" class="d-none">
                          <label for="upload" class="form-label w-100 text-center">Ajuste a imagem</label>
                          <div id="croppie-field"></div>
                          <div class="mx-0 text-center mb-5">
                              <button class="btn  btn-info rounded-circle" id="rotate-left" type="button"><i class="ri-reset-left-line ri-1x"></i></button>
                              <button class="btn  btn-info rounded-circle" id="rotate-right" type="button"><i class="ri-reset-right-line ri-1x"></i></button>
                              <button class="btn  btn-info rounded-5" id="upload-btn" type="button"><i class="ri-save-3-line ri-1x"></i> Salvar</button>
                              <button class="btn  btn-info rounded-5 btn-cancelar" type="button"><i class="ri-close-circle-fill ri-1x"></i> Cancelar</button>
                          </div>
                      </div>
                  </div>
              </div>
              <div class="col-md-8 col-sm-12 mx-auto" id="menssagem"></div>
              <div class="modal-body">
                  <div class="row g-3" id="listaImagens">
                      <!-- imagens via AJAX -->
                  </div>
              </div>

              <div class="modal-footer justify-content-between">
                  <div>
                      <div id="divPaginacao" class="">
                          <button class="btn btn-outline-secondary btn-sm btn-prev" onclick="carregarImagens(paginaAtual - 1)">Anterior</button>
                          <button class="btn btn-outline-secondary btn-sm btn-next" onclick="carregarImagens(paginaAtual + 1)">Próxima</button>
                      </div>
                  </div>
                  <button class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
              </div>

          </div>
      </div>
  </div>


  <script>
      let paginaAtual = 1;
      let campoDestino = '';

      function abrirModalImagens(campo) {
          campoDestino = campo;
          paginaAtual = 1;
          carregarImagens(1);
          new bootstrap.Modal(document.getElementById('modalImagens')).show();
          $('#upload').val(null);
          $('#croppie-editor').addClass('d-none');
          $('#divDeleteImagem').addClass('d-none');
          $('#divAdd').removeClass('d-none');
          $('#listaImagens').removeClass('d-none');
          $('#divPaginacao').removeClass('d-none');
      }

      function carregarImagens(pagina) {
          if (pagina < 1) return;

          paginaAtual = pagina;

          fetch('<?php echo $dominio ?>/app/carrega_imagens.php?p=' + pagina)
              .then(r => r.text())
              .then(html => {
                  const lista = document.getElementById('listaImagens');
                  lista.innerHTML = html;

                  const meta = document.getElementById('metaPag');
                  if (!meta) return;

                  const paginaAtualAjax = parseInt(meta.dataset.pagina);
                  const ultima = parseInt(meta.dataset.ultima);

                  document.querySelector('.btn-prev').disabled = (paginaAtualAjax <= 1);
                  document.querySelector('.btn-next').disabled = (paginaAtualAjax >= ultima);
              });
      }

      function selecionarImagem(nome) {
          document.getElementById(campoDestino).value = nome;

          const preview = document.getElementById('preview_' + campoDestino);
          preview.src = '<?php echo $dominio ?>/app/imagens/uploads/' + nome;
          preview.style.display = 'block';

          bootstrap.Modal.getInstance(document.getElementById('modalImagens')).hide();
      }

      var $croppie = new Croppie($('#croppie-field')[0], {
          enableExif: true,
          enableResize: true,
          enableZoom: true,
          boundary: {
              width: 400,
              height: 400
          },
          viewport: {
              height: 300,
              width: 300
          },
          enableOrientation: true
      })

      $(document).ready(function() {
          var img_name;
          // console.log($croppie)
          $('#upload').on('change', function(e) {
              var reader = new FileReader();
              img_name = e.target.files[0].name;
              reader.onload = function(e) {
                  $croppie.bind({
                      url: e.target.result
                  });
                  $('#croppie-editor').removeClass('d-none')
                  $('#divDeleteImagem').addClass('d-none');
                  $('#divAdd').addClass('d-none');
                  $('#listaImagens').addClass('d-none');
                  $('#divPaginacao').addClass('d-none');
              }
              reader.readAsDataURL(this.files[0]);
          })


          $('#rotate-left').click(function() {
              $croppie.rotate(90);
          })
          $('#rotate-right').click(function() {
              $croppie.rotate(-90);

          })
          $('#upload-btn').click(function() {
              $croppie.result({
                  type: 'base64',
                  format: 'png'
              }).then((imgBase64) => {
                  $.ajax({
                      url: '<?php echo $dominio ?>/app/upload_imagem.php',
                      method: 'POST',
                      data: {
                          'img': imgBase64,
                          'fname': img_name
                      },
                      dataType: 'json',
                      error: err => {
                          console.error(err)
                      },
                      success: function(response) {
                          if (response.status == 'success') {
                              $('#croppie-editor').addClass('d-none');
                              $('#divDeleteImagem').addClass('d-none');
                              $('#divAdd').removeClass('d-none');
                              $('#listaImagens').removeClass('d-none');
                              $('#divPaginacao').removeClass('d-none');
                              carregarImagens(paginaAtual)
                              $('#menssagem').html('<div class="alert alert-success alert-dismissible fade show" id="alertAuto" role="alert" data-pg-collapsed><i class="ri-check-fill"></i><strong> Sucesso </strong>Imagem adicionada com sucesso!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                          } else {
                              $('#menssagem').html('<div class="alert alert-danger alert-dismissible fade show" id="alertAuto" role="alert" data-pg-collapsed><i class="ri-error-warning-line"></i><strong> Erro </strong> ' + response + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                          }
                      }
                  })
              })
              setTimeout(function() {
                  $('#alertAuto').fadeOut(400, function() {
                      $(this).remove();
                  });
              }, 5000);
          })
      })

      $(document).on('click', '.btn-deletar', function() {

          let id = $(this).data('id');
          let img = $(this).data('img');

          $('#previewDeletar').attr('src', '<?php echo $dominio ?>/app/imagens/uploads/' + img);
          $('#divDeleteImagem').removeClass('d-none');
          $('#divAdd').addClass('d-none');
          $('#listaImagens').addClass('d-none');
          $('#divPaginacao').addClass('d-none');
          $('#btnDelete').html('<button type="button" class="btn btn-lg btn-link fs-6 text-decoration-none col-6 m-0 rounded-0 border-right btnExcluirImagem" data-id="' + id + '" data-img="' + img + '" title="Excluir"><strong>Excluír</strong></button>');
      });

      $(document).on('click', '.btn-cancelar', function() {
          $('#upload').val(null);
          $('#croppie-editor').addClass('d-none');
          $('#divDeleteImagem').addClass('d-none');
          $('#divAdd').removeClass('d-none');
          $('#listaImagens').removeClass('d-none');
          $('#divPaginacao').removeClass('d-none');
      });



      $(document).on('click', '.btnExcluirImagem', function() {

          let id = $(this).data('id');
          let img = $(this).data('img');

          $.ajax({
              url: '<?php echo $dominio ?>/app/deletar_imagens.php',
              type: 'POST',
              data: {
                  id,
                  img
              },
              success: function(resp) {
                  carregarImagens(paginaAtual);
                  $('#divDeleteImagem').addClass('d-none');
                  $('#divAdd').removeClass('d-none');
                  $('#listaImagens').removeClass('d-none');
                  $('#divPaginacao').removeClass('d-none');
                  $('#menssagem').html('<div class="alert alert-success alert-dismissible fade show" id="alertAuto" role="alert" data-pg-collapsed><i class="ri-check-fill"></i><strong> Sucesso </strong> ' + resp + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
              },
              error: function() {
                  $('#menssagem').html('<div class="alert alert-danger alert-dismissible fade show" id="alertAuto" role="alert" data-pg-collapsed><i class="ri-error-warning-line"></i><strong> Erro </strong> Erro ao excluir<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
              }

          });

          setTimeout(function() {
              $('#alertAuto').fadeOut(400, function() {
                  $(this).remove();
              });
          }, 5000);

      });
  </script>