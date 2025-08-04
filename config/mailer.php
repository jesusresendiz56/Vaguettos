<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

function enviarCorreoRecuperacion($correoDestino, $token) {
    $mail = new PHPMailer(true);
    try {
        // Configuración SMTP Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'zenchat0602@gmail.com';
        $mail->Password = 'gwrb umdp cdft mnhv';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('zenchat0602@gmail.com', 'Vaguettos');
        $mail->addAddress($correoDestino);

        $mail->isHTML(true);
        $mail->Subject = 'Recuperacion de contraseña vaguettos';
        $mail->Body = "
    <p>Hola,</p>
    <p>Has solicitado recuperar tu contraseña en <strong>vaguettos</strong>.</p>
    <p>Tu código para restablecer la contraseña es:</p>
    <p style='font-size: 24px; font-weight: bold; color: #000000;'>{$token}</p>
    <p>Este código es válido por <strong>5 minutos</strong>.</p>
    <p>Si no solicitaste este cambio, puedes ignorar este mensaje.</p>
    <br>
    <p>Saludos,<br><strong>vaguettos</strong></p>
";


        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Error enviando correo: {$mail->ErrorInfo}");
        return false;
    }
}
