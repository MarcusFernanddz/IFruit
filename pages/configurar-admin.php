<?php
require_once __DIR__ . '/../connect/auth.php';
require_once __DIR__ . '/../connect/conexao.php';

$erro = '';
$sucesso = '';
$esquemaPronto = esquemaAutenticacaoPronto($con);
$contas = $esquemaPronto ? mysqli_query($con, 'SELECT id_administrador, senha FROM administrador') : null;
$primeiroAdministrador = null;
$configuracaoFechada = !$esquemaPronto;

if (!$esquemaPronto) {
    $erro = 'Aplique primeiro o arquivo ifruit_auth_migration.sql, conforme as instruções de AUTENTICACAO.md.';
}

while ($contas && $conta = mysqli_fetch_assoc($contas)) {
    if ((int) $conta['id_administrador'] === 1) {
        $primeiroAdministrador = $conta;
    }
    if (password_get_info($conta['senha'])['algo'] !== null) {
        $configuracaoFechada = true;
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $senha = $_POST['senha'] ?? '';
    $confirmacao = $_POST['confirmacao'] ?? '';

    if (!validarTokenCsrf($_POST['token_csrf'] ?? null)) {
        $erro = 'Sua sessão expirou. Atualize a página e tente novamente.';
    } elseif (!$esquemaPronto) {
        $erro = 'Aplique primeiro o arquivo ifruit_auth_migration.sql, conforme as instruções de AUTENTICACAO.md.';
    } elseif ($configuracaoFechada) {
        $erro = 'A configuração inicial já foi concluída.';
    } elseif ($nome === '' || mb_strlen($nome) > 120) {
        $erro = 'Informe um nome com até 120 caracteres.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 80) {
        $erro = 'Informe um e-mail válido com até 80 caracteres.';
    } elseif (strlen($senha) < 10 || strlen($senha) > 72) {
        $erro = 'A senha deve ter entre 10 e 72 caracteres.';
    } elseif ($senha !== $confirmacao) {
        $erro = 'A confirmação de senha não corresponde.';
    } else {
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        if ($primeiroAdministrador) {
            $gravar = mysqli_prepare($con, "UPDATE administrador SET nome = ?, email = ?, senha = ?, nivel_acesso = 'admin', ativo = 1 WHERE id_administrador = 1");
            if ($gravar) {
                mysqli_stmt_bind_param($gravar, 'sss', $nome, $email, $hash);
            }
        } else {
            $gravar = mysqli_prepare($con, "INSERT INTO administrador (nome, email, senha, nivel_acesso, ativo) VALUES (?, ?, ?, 'admin', 1)");
            if ($gravar) {
                mysqli_stmt_bind_param($gravar, 'sss', $nome, $email, $hash);
            }
        }

        if ($gravar && mysqli_stmt_execute($gravar)) {
            $sucesso = 'Administrador configurado. Agora entre usando o e-mail e a senha definidos.';
            $configuracaoFechada = true;
        } else {
            $erro = 'Não foi possível salvar. Verifique se o e-mail já está cadastrado.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuração inicial - IFruit</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
<div class="bg"></div>
<main class="page">
    <section class="side-left">
        <div class="badge"><span class="badge-dot"></span><span>IF Campus Morrinhos</span></div>
        <div class="brand">IF<em>ruit</em></div>
        <p class="tagline">Configure a primeira conta administrativa para começar a usar o sistema.</p>
        <div class="campus-info">Instituto Federal Goiano &mdash; Campus Morrinhos</div>
    </section>
    <section class="side-right">
        <div class="card">
            <div class="card-header">
                <h2>Administrador inicial</h2>
                <p>Esta configuração só pode ser concluída uma vez.</p>
            </div>
            <?php if ($erro): ?>
                <div class="alert-erro"><span aria-hidden="true">!</span><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>
            <?php if ($sucesso): ?>
                <div class="alert-sucesso"><?= htmlspecialchars($sucesso) ?></div>
            <?php endif; ?>
            <?php if (!$configuracaoFechada): ?>
                <form method="post">
                    <?= campoTokenCsrf() ?>
                    <div class="field">
                        <label for="nome">Nome</label>
                        <div class="input-wrap"><input type="text" id="nome" name="nome" maxlength="120" required autocomplete="name" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>"></div>
                    </div>
                    <div class="field">
                        <label for="email">E-mail</label>
                        <div class="input-wrap"><input type="email" id="email" name="email" maxlength="80" required autocomplete="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"></div>
                    </div>
                    <div class="field">
                        <label for="senha">Senha (de 10 a 72 caracteres)</label>
                        <div class="input-wrap"><input type="password" id="senha" name="senha" minlength="10" maxlength="72" required autocomplete="new-password"></div>
                    </div>
                    <div class="field">
                        <label for="confirmacao">Confirme a senha</label>
                        <div class="input-wrap"><input type="password" id="confirmacao" name="confirmacao" minlength="10" maxlength="72" required autocomplete="new-password"></div>
                    </div>
                    <button type="submit" class="btn-entrar">Configurar administrador</button>
                </form>
            <?php endif; ?>
            <div class="card-footer"><a href="login (1).php">Voltar para o login</a></div>
        </div>
    </section>
</main>
</body>
</html>