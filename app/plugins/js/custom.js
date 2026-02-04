// funçoões para uploand de arrasta ou selecionat a imagem
const inputFile = document.querySelector("#picture__input");
const pictureImage = document.querySelector(".picture__image");
const pictureImageTxt = "Escolha uma imagem faça os ajustes e<br> clique em enviar para fazer upload do arquivo";
pictureImage.innerHTML = pictureImageTxt;

// funçoões para redimencionar e recortar a imagem
// Carregar o espaço para o preview da imagem
var redimensionar = $('#preview').croppie({

    // Ativar a leitura de orientação para renderizar corretamente a imagem
    enableExif: true,
    enableZoom: true, // Habilita o zoom
    // O recipiente interno do coppie. A parte visível da imagem
    viewport: {
        width: '300',
        height: 200,
        type: 'square'
    },

    // O recipiente externo do cortador
    boundary: {
        width: '450',
        height: 300
    }
});

// Inicialize o zoom
var currentZoom = 1;

// Aumentar o zoom
$('#zoom-in').on('click', function () {
    currentZoom += 0.1;
    croppie.setZoom(currentZoom);
});

// Diminuir o zoom
$('#zoom-out').on('click', function () {
    currentZoom -= 0.1;
    croppie.setZoom(currentZoom);
});



const selectElement = document.querySelector("#picture__input");

selectElement.addEventListener("change", (event) => {

  const fileInput = document.querySelector('#picture__input');
  const file = fileInput.files[0];
  const reader = new FileReader();
    var TamanhoValida = new Boolean(true);
  
    var aviso = document.getElementById('aviso');
  reader.addEventListener('load', () => {
    var imagem = document.createElement("img");
    var intHeight = 1199;
    var intWidth = 1799;
    imagem.addEventListener('load', () => {

       if (imagem.height < intHeight) {
            TamanhoValida = false;
        }
        if (imagem.width < intWidth) {
            TamanhoValida = false;
        }
        if (TamanhoValida == true) {
            aviso.innerHTML = '';
            redimensionar.croppie('bind', {
                // Recuperar a imagem base64
                url: reader.result
            });
            document.getElementById('btn_enviar').removeAttribute('disabled');
        }
        if (TamanhoValida == false) {
               alert('Imagem não atende os requesitos!');
             $('img').attr('src', '');
            aviso.innerHTML = '<div class="alert alert-danger alert-dismissible fade show" role="alert" data-pg-collapsed><strong>Sua imagem tem Largura:' + imagem.width + 'px Altura:' + imagem.height + 'px !</strong> </ br> Sua imagem é menor que 1800px de largura por 1200px de altura </ br> use outra imagem ou clique no botão para ampliar </ br> <a href="?pg=cl&cl=up" class="btn btn-primary btn-sm active" role="button" aria-pressed="true">Ampliar</a>.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            document.getElementById('btn_enviar').setAttribute('disabled', '');
        }
    });
    imagem.src = reader.result;
  });

  reader.readAsDataURL(file);
});


// Executar a instrução quando o usuário clicar no botão enviar
$('.btn-upload-imagem').on('click', function () {
    var desricao = document.getElementById('desricao').value;
    var w = parseInt(1800, 10),
            h = parseInt(1200, 10), s
    size = 'viewport';
    now = new Date
    nome = now.getTime();
    if (w || h) {
        size = {width: w, height: h};
    }
    redimensionar.croppie('result', {
        type: 'canvas', // Tipo de arquivos permitidos - base64, html, blob
        size: size, // O tamanho da imagem cortada
        resultSize: {
            width: 50,
            height: 50
        }
    }).then(function (img) {

        // Enviar os dados para um arquivo PHP
        $.ajax({
            url: "clientes/paisagem/upload/upload.php?desricao=" + desricao + "&image=" + nome, // Enviar os dados para o arquivo upload.php
            type: "POST", // Método utilizado para enviar os dados
            data: {// Dados que deve ser enviado
                "imagem": img
            },
            success: function () {
                // sweetalert - https://celke.com.br/artigo/como-usar-sweetalert-no-formulario-com-javascript-e-php
                window.location = "?pg=cl&cl=pp&mg=" + nome;
            }
        });
    });
});





