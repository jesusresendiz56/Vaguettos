<?php
require_once '../modelo/conexion2.php';
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo = $_POST["usuario_correo"];
    $codigo = rand(100000, 999999);
    $expira = date("Y-m-d H:i:s", strtotime("+20 minutes"));

    $stmt = $conn2->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->close();
        $update = $conn2->prepare("UPDATE usuarios SET codigo_recuperacion = ?, expiracion_codigo = ? WHERE correo = ?");
        $update->bind_param("sss", $codigo, $expira, $correo);
        $update->execute();

        // Enviar el correo
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'jesusrresendiz789@gmail.com'; 
            $mail->Password = 'evus cbwh qrcc fvtb'; 
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('jesusrresendiz789@gmail.com', 'Vaguettos');
            $mail->addAddress($correo);
            $mail->isHTML(true);
            $mail->Subject = 'Codigo de recuperacion';
            $mail->Body = "
            <p>Hola,</p>
            <p>Has solicitado recuperar tu contraseña en <strong>Vaguettos</strong>.</p>
            <p>Tu código para restablecer la contraseña es:</p>
            <p style='font-size: 24px; font-weight: bold; color: #000000;'>{$codigo}</p>
            <p>Este codigo es valido por <strong>5 minutos</strong>.</p>
            <p>Si no solicitaste este cambio, puedes ignorar este mensaje.</p>
            <br>
            <p>Saludos,<br><strong>Vaguettos</strong></p>
            ";

            $mail->send();
            header("Location: ../vista/formulario_codigo.php?correo=" . urlencode($correo));
            exit;
        } catch (Exception $e) {
            echo "Error al enviar el correo: " . $mail->ErrorInfo;
        }
    } else {
        echo "Correo no registrado.";
    }
}
?>
