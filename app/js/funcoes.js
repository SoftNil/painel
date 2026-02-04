$('.carousel .mine .carousel-item').each(function () {
var minPerSlide = 6;
var next = $(this).next();
if (!next.length) {
next = $(this).siblings(':first');
}
next.children(':first-child').clone().appendTo($(this));

for (var i = 0; i < minPerSlide; i++) { next=next.next(); if (!next.length) { next=$(this).siblings(':first'); } next.children(':first-child').clone().appendTo($(this)); } });

function SomaValores() {
    var valor_servico = 0;
    var total_final = 0;
    var TotalDias = 1;
    var valor = 0;
    var string = '';
    var retorno = '';
    var dia = 0;
    var y = 0;
    var id_cab = '';
    var select = document.getElementsByName('select_group');
    for (i = 0; i < select.length; i++) {
        var e = select[i];
        if (e.value != '') {
            string = e.value;
            retorno = string.split("|");
            valor = retorno[1];
            valor_servico = parseFloat(valor_servico) + parseFloat(valor);
        }
    }
    document.getElementById('valor_servico').value = valor_servico;
    var radio = document.getElementsByName('gridRadios');
    for (i = 0; i < radio.length; i++) {
        var e = radio[i];
        string = e.value;
        retorno = string.split("|");
        var id = retorno[0];
        var preco = retorno[1];
        var quantidade = retorno[2];
        var nome = 'radios_preco' + id;
        preco = parseFloat(preco) + parseFloat(valor_servico);
        var total = preco * quantidade;
        var porcentagem = parseFloat(total * (retorno[3] / 100));
        var desconto = retorno[4];
        total = total - porcentagem;
        if (desconto != 0 && porcentagem == 0) {
            total = total - desconto;
        }
        document.getElementById(nome).innerHTML = total.toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
        if (e.checked == true) {
            total_final = total;
            document.getElementById('quantidade').innerHTML = quantidade;
            document.getElementById('quantidade2').innerHTML = quantidade;
        }
    }

    var check = document.getElementsByClassName('classcheckboxs');
    for (i = 0; i < check.length; i++) {
        var checkboxs = check[i];
        if (checkboxs.checked == true) {
            string = checkboxs.value;
            retorno = string.split("|");
            dia = retorno[0];
            valor = retorno[1];
            total_final = parseFloat(total_final) + parseFloat(valor);
            TotalDias = parseFloat(TotalDias) + parseFloat(dia);
        }
    }

    var checkop = document.getElementsByClassName('checkopcionais');
    for (i = 0; i < checkop.length; i++) {
        var checkboxsop = checkop[i];
        if (checkboxsop.checked == true) {
            y++;
            string = checkboxsop.value;
            retorno = string.split("|");
            id_cab = id_cab + '|' + retorno[4];

        }
    }
    document.getElementById('acabamentosopcionais').value = y + id_cab;
    if (TotalDias > 1) {
        document.getElementById('dias').innerHTML = TotalDias + ' dias úteis'
    }
    if (TotalDias == 1) {
        document.getElementById('dias').innerHTML = TotalDias + ' dia útil'
    }
    var vezes = document.getElementById('vezes').value;
    if (Number(vezes) < 1) {
        vezes = 1;
    }
    if (Number(vezes) > 10) {
        vezes = 10;
    }
    document.getElementById('vezes').value = vezes;
    total_final = total_final * vezes;
    document.getElementById('valorpagar').value = total_final.toFixed(2);
    document.getElementById('preco').innerHTML = total_final.toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
}

function SoNumerosInputText(evt) {
    var theEvent = evt || window.event;
    var key = theEvent.keyCode || theEvent.which;
    key = String.fromCharCode(key);
    var regex = /^[0-9.]+$/;
    if (!regex.test(key)) {
        theEvent.returnValue = false;
        if (theEvent.preventDefault)
            theEvent.preventDefault();
    }
}

function AumentaVezesCarrinho(id) {
    var vezesid = 'vezes' + id;
    var vezes = document.getElementById(vezesid).value;
    vezes = Number(vezes) + 1;
    if (vezes > 10) {
        vezes = 10;
    }
    document.getElementById(vezesid).value = vezes;
    CarrinhoSomaValores(id);
}

function DiminueVezesCarrinho(id) {
    var vezesid = 'vezes' + id;
    var vezes = document.getElementById(vezesid).value;
    vezes = Number(vezes) - 1;
    if (vezes < 1) {
        vezes = 1;
    }
    document.getElementById(vezesid).value = vezes;
    CarrinhoSomaValores(id);
}

function PegaPG(numero) { 
    document.getElementById('pg').value = numero;
}

function PegaDeletar(numero,imagem) { 
    document.getElementById('deletar').value = numero;
    document.getElementById('ImagemDeletar').src = imagem; 

}

