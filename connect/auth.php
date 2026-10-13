<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('IFRUITSESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function usuarioAtual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function esquemaAutenticacaoPronto(mysqli $con): bool
{
    try {
        $necessarias = [
            'administrador' => ['nivel_acesso', 'ativo', 'criado_por'],
            'venda' => ['registrado_por_email', 'registrado_por_nome'],
        ];
        foreach ($necessarias as $tabela => $colunasNecessarias) {
            $resultado = mysqli_query($con, "SHOW COLUMNS FROM `$tabela`");
            $colunasEncontradas = [];
            while ($resultado && $coluna = mysqli_fetch_assoc($resultado)) {
                $colunasEncontradas[] = $coluna['Field'];
            }
            if (array_diff($colunasNecessarias, $colunasEncontradas)) {
                return false;
            }
        }
    } catch (Throwable $erro) {
        return false;
    }

    return true;
}

function exigirLogin(): void
{
    if (!usuarioAtual()) {
        header('Location: login (1).php');
        exit;
    }

    static $idValidado = null;
    $idUsuario = (int) $_SESSION['usuario']['id'];
    if ($idValidado === $idUsuario) {
        return;
    }

    global $con, $conn;
    require_once __DIR__ . '/conexao.php';
    $consulta = mysqli_prepare($con, 'SELECT nome, email, nivel_acesso, ativo FROM administrador WHERE id_administrador = ? LIMIT 1');
    if (!$consulta) {
        http_response_code(503);
        exit('Não foi possível validar a conta.');
    }

    mysqli_stmt_bind_param($consulta, 'i', $idUsuario);
    mysqli_stmt_execute($consulta);
    $resultado = mysqli_stmt_get_result($consulta);
    $conta = $resultado ? mysqli_fetch_assoc($resultado) : null;
    if (!$conta || !(int) $conta['ativo']) {
        $_SESSION = [];
        session_destroy();
        header('Location: login (1).php');
        exit;
    }

    $_SESSION['usuario']['nome'] = $conta['nome'];
    $_SESSION['usuario']['email'] = $conta['email'];
    $_SESSION['usuario']['nivel_acesso'] = $conta['nivel_acesso'];
    $idValidado = $idUsuario;
}

function exigirAdministrador(): void
{
    exigirLogin();

    if (($_SESSION['usuario']['nivel_acesso'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Acesso permitido apenas para administrador.');
    }
}

function tokenCsrf(): string
{
    if (empty($_SESSION['token_csrf'])) {
        $_SESSION['token_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['token_csrf'];
}

function validarTokenCsrf($token): bool
{
    return isset($_SESSION['token_csrf'])
        && is_string($token)
        && hash_equals($_SESSION['token_csrf'], $token);
}

function campoTokenCsrf(): string
{
    return '<input type="hidden" name="token_csrf" value="'
        . htmlspecialchars(tokenCsrf(), ENT_QUOTES, 'UTF-8') . '">';
}