<?php
session_start();
$_SESSION['ultimo_acceso'] = time();
http_response_code(200);
?>
