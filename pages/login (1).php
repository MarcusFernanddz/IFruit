<?php
session_start();

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        $erro = 'Preencha o e-mail e a senha.';
    } else {
        header('Location: CadCliente.php');
        exit;
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
            <div class="card-footer">IFruit &copy; <?= date('Y') ?> &mdash; IF Goiano Campus Morrinhos</div>
        </div>
    </div>
</div>
</body>
</html>
