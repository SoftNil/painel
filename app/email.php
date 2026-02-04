<?php
header('Content-Type: text/html; charset=UTF-8');
//include 'topo.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './plugins/PHPMailer/src/Exception.php';
require './plugins/PHPMailer/src/PHPMailer.php';
require './plugins/PHPMailer/src/SMTP.php';

$query_6 = "SELECT * FROM smtp_6 "
        . "WHERE id_6 = '$id_smtp_4'";
$sql_6 = mysqli_query($conecta, $query_6);
$cont_6 = mysqli_num_rows($sql_6);
while ($linha_6 = mysqli_fetch_assoc($sql_6)) {
    $nome_6 = $linha_6['nome_6'];
    $auth_6 = $linha_6['auth_6'];
    $host_6 = $linha_6['host_6'];
    $port_6 = $linha_6['port_6'];
    $secure_6 = $linha_6['secure_6'];
    $username_6 = $linha_6['username_6'];
    $password_6 = $linha_6['password_6'];
}

//Variaveis do formulário
function EnviarEmail($nome, $email, $sobre, $menssagem_html, $menssagem) {
// Instância da classe
    
    global  $titulo_4, $nome_6, $auth_6, $host_6, $port_6, $secure_6, $username_6, $password_6;
    
    $mail = new PHPMailer(true);
    try {
        // Configurações do servidor
        $mail->isSMTP();  
        $mail->CharSet = 'UTF-8';
        //Devine o uso de SMTP no envio
        $mail->setLanguage('pt_br', './plugins/PHPMailer/language/');
        $mail->SMTPAuth = $auth_6; //Habilita a autenticação SMTP
        $mail->Username = $username_6;
        $mail->Password = descriptografa($password_6);
        // Criptografia do envio SSL também é aceito
        $mail->SMTPSecure = $secure_6;
        // Informações específicadas pelo Google
        $mail->Host = $host_6;
        $mail->Port = $port_6;

        // Define o remetente
        $mail->setFrom($username_6, $titulo_4);
        // Define o destinatário
        $mail->addAddress($email, $nome);
        // Conteúdo da mensagem
        $mail->isHTML(true);  // Seta o formato do e-mail para aceitar conteúdo HTML
        $mail->Subject = $sobre;
        $mail->Body = $menssagem_html;
        $mail->AltBody = $menssagem;
        // Enviar
        $mail->send();
        return 'sucesso';
    } catch (Exception $e) {
        return "A mensagem não pôde ser enviada. Erro do Mailer: {$mail->ErrorInfo}";
    }
}
?>