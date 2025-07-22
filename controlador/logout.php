<?php
session_start();

// Solo cerrar sesión si está activa
if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION)) {
    session_unset();
    session_destroy();

    // Eliminar cookie de sesión si aplica
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
}

// Redirigir al inicio
header("Location: ../vista/index.php");
exit();
?>
