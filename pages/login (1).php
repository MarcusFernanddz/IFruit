<?php
require_once __DIR__ . '/../connect/auth.php';
require_once __DIR__ . '/../connect/conexao.php';

$erro = '';

if (usuarioAtual()) {
    header('Location: Venda.php');
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (!validarTokenCsrf($_POST['token_csrf'] ?? null)) {
        $erro = 'Sua sessão expirou. Atualize a página e tente novamente.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || $senha === '') {
        $erro = 'Informe um e-mail válido e a senha.';
    } elseif (!esquemaAutenticacaoPronto($con)) {
        $erro = 'A atualização do banco ainda não foi aplicada. Siga as instruções de AUTENTICACAO.md.';
    } else {
        $consulta = mysqli_prepare($con, 'SELECT id_administrador, nome, email, senha, nivel_acesso, ativo FROM administrador WHERE email = ? LIMIT 1');
        if (!$consulta) {
            $erro = 'Não foi possível consultar as contas. Aplique a atualização do banco de dados e tente novamente.';
        } else {
            mysqli_stmt_bind_param($consulta, 's', $email);
            mysqli_stmt_execute($consulta);
            $resultado = mysqli_stmt_get_result($consulta);
            $conta = $resultado ? mysqli_fetch_assoc($resultado) : null;

            if (!$conta || !(int) $conta['ativo'] || !password_verify($senha, $conta['senha'])) {
                $erro = 'E-mail ou senha incorretos, ou conta desativada.';
            } else {
                if (password_needs_rehash($conta['senha'], PASSWORD_DEFAULT)) {
                    $novoHash = password_hash($senha, PASSWORD_DEFAULT);
                    $atualizar = mysqli_prepare($con, 'UPDATE administrador SET senha = ? WHERE id_administrador = ?');
                    mysqli_stmt_bind_param($atualizar, 'si', $novoHash, $conta['id_administrador']);
                    mysqli_stmt_execute($atualizar);
                }

                session_regenerate_id(true);
                $_SESSION['usuario'] = [
                    'id' => (int) $conta['id_administrador'],
                    'nome' => $conta['nome'],
                    'email' => $conta['email'],
                    'nivel_acesso' => $conta['nivel_acesso'],
                ];
                unset($_SESSION['token_csrf']);
                header('Location: Venda.php');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IFruit - Entrar</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
<div class="bg"></div>
<div class="page">
    <div class="side-left">
        <div class="badge"><span class="badge-dot"></span><span>IF Campus Morrinhos</span></div>
        <div class="brand">IF<em>ruit</em></div>
        <p class="tagline">Gestão de vendas de excedentes agrícolas do campus. Simples, rápido e organizado.</p>
        <div class="campus-info">Instituto Federal Goiano &mdash; Campus Morrinhos</div>
    </div>
    <div class="side-right">
        <div class="card">
            <div class="card-header">
                <h2>Bem-vindo</h2>
                <p>Entre com suas credenciais para acessar o sistema</p>
            </div>
            <?php if ($erro): ?>
                <div class="alert-erro"><span aria-hidden="true">!</span><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <?= campoTokenCsrf() ?>
                <div class="field">
                    <label for="email">E-mail</label>
                    <div class="input-wrap">
                        <span aria-hidden="true">@</span>
                        <input type="email" id="email" name="email" placeholder="seu@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autocomplete="email">
                    </div>
                </div>
                <div class="field">
                    <label for="senha">Senha</label>
                    <div class="input-wrap">
                        <span aria-hidden="true">*</span>
                        <input type="password" id="senha" name="senha" placeholder="Senha" required autocomplete="current-password">
                    </div>
                </div>
                <button type="submit" class="btn-entrar">Entrar no sistema</button>
            </form>
            <div class="card-footer">IFruit &copy; <?= date('Y') ?> &mdash; IF Goiano Campus Morrinhos<br><a href="configurar-admin.php">Configuração inicial do administrador</a></div>
        </div>
    </div>
</div>
</body>
</html>
