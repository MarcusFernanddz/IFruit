<?php
require_once __DIR__ . '/../connect/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !validarTokenCsrf($_POST['token_csrf'] ?? null)) {
    http_response_code(400);
    exit('Requisição inválida.');
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $parametros = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $parametros['path'], $parametros['domain'], $parametros['secure'], $parametros['httponly']);
}
session_destroy();
header('Location: login (1).php');
exit;