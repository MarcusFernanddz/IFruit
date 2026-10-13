<?php
require_once __DIR__ . '/../connect/auth.php';
exigirAdministrador();
require_once __DIR__ . '/../connect/conexao.php';

$erro = '';
$mensagem = '';
$usuarioLogado = usuarioAtual();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $acao = $_POST['acao'] ?? '';
    if (!validarTokenCsrf($_POST['token_csrf'] ?? null)) {
        $erro = 'Sua sessão expirou. Atualize a página e tente novamente.';
    } elseif ($acao === 'criar_estagiario') {
        $nome = trim($_POST['nome'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $senha = $_POST['senha'] ?? '';

        if ($nome === '' || mb_strlen($nome) > 120) {
            $erro = 'Informe um nome com até 120 caracteres.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 80) {
            $erro = 'Informe um e-mail válido com até 80 caracteres.';
        } elseif (strlen($senha) < 10 || strlen($senha) > 72) {
            $erro = 'A senha deve ter entre 10 e 72 caracteres.';
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $idCriador = (int) $usuarioLogado['id'];
            $inserir = mysqli_prepare($con, "INSERT INTO administrador (nome, email, senha, nivel_acesso, ativo, criado_por) VALUES (?, ?, ?, 'estagiario', 1, ?)");
            if ($inserir) {
                mysqli_stmt_bind_param($inserir, 'sssi', $nome, $email, $hash, $idCriador);
            }
            if ($inserir && mysqli_stmt_execute($inserir)) {
                $mensagem = 'Conta de estagiário criada.';
            } else {
                $erro = 'Não foi possível criar a conta. Verifique se o e-mail já está cadastrado.';
            }
        }
    } elseif (in_array($acao, ['desativar', 'reativar'], true)) {
        $idAlvo = (int) ($_POST['id_usuario'] ?? 0);
        if ($idAlvo <= 0 || $idAlvo === (int) $usuarioLogado['id']) {
            $erro = 'Esta conta não pode ser alterada.';
        } else {
            $ativo = $acao === 'reativar' ? 1 : 0;
            $atualizar = mysqli_prepare($con, 'UPDATE administrador SET ativo = ? WHERE id_administrador = ? AND nivel_acesso = \'estagiario\'');
            if ($atualizar) {
                mysqli_stmt_bind_param($atualizar, 'ii', $ativo, $idAlvo);
            }
            if ($atualizar && mysqli_stmt_execute($atualizar) && mysqli_stmt_affected_rows($atualizar) === 1) {
                $mensagem = $ativo ? 'Conta de estagiário reativada.' : 'Conta de estagiário desativada.';
            } else {
                $erro = 'Não foi possível alterar a conta. Apenas contas de estagiário podem ser alteradas aqui.';
            }
        }
    }
}

$usuarios = mysqli_query($con, 'SELECT a.id_administrador, a.nome, a.email, a.nivel_acesso, a.ativo, a.criado_em, criador.nome AS nome_criador FROM administrador a LEFT JOIN administrador criador ON criador.id_administrador = a.criado_por ORDER BY a.nivel_acesso, a.nome');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contas de acesso - IFruit</title>
    <link rel="stylesheet" href="../css/sidebar.css?v=2">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/usuarios.css">
</head>
<body>
<?php $paginaAtiva = 'usuarios'; require_once 'sidebar.php'; ?>
<main class="usuarios-container">
    <header class="usuarios-cabecalho">
        <div><p>Administração</p><h1>Contas de acesso</h1></div>
    </header>

    <?php if ($erro): ?><div class="usuarios-alerta erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
    <?php if ($mensagem): ?><div class="usuarios-alerta sucesso"><?= htmlspecialchars($mensagem) ?></div><?php endif; ?>

    <section class="usuarios-secao">
        <h2>Registrar estagiário</h2>
        <form class="usuarios-formulario" method="post">
            <?= campoTokenCsrf() ?>
            <input type="hidden" name="acao" value="criar_estagiario">
            <label>Nome<input type="text" name="nome" maxlength="120" required autocomplete="name"></label>
            <label>E-mail<input type="email" name="email" maxlength="80" required autocomplete="email"></label>
            <label>Senha inicial<input type="password" name="senha" minlength="10" maxlength="72" required autocomplete="new-password"></label>
            <button type="submit">Criar conta</button>
        </form>
    </section>

    <section class="usuarios-secao">
        <h2>Usuários cadastrados</h2>
        <div class="usuarios-tabela-wrap">
            <table class="usuarios-tabela">
                <thead><tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Status</th><th>Cadastrado por</th><th>Ação</th></tr></thead>
                <tbody>
                <?php while ($usuario = $usuarios ? mysqli_fetch_assoc($usuarios) : null): ?>
                    <tr>
                        <td><?= htmlspecialchars($usuario['nome']) ?></td>
                        <td><?= htmlspecialchars($usuario['email']) ?></td>
                        <td><?= $usuario['nivel_acesso'] === 'admin' ? 'Administrador' : 'Estagiário' ?></td>
                        <td><?= (int) $usuario['ativo'] ? 'Ativo' : 'Desativado' ?></td>
                        <td><?= htmlspecialchars($usuario['nome_criador'] ?? ($usuario['nivel_acesso'] === 'admin' ? 'Configuração inicial' : '-')) ?></td>
                        <td>
                            <?php if ($usuario['nivel_acesso'] === 'estagiario'): ?>
                                <form method="post">
                                    <?= campoTokenCsrf() ?>
                                    <input type="hidden" name="id_usuario" value="<?= (int) $usuario['id_administrador'] ?>">
                                    <input type="hidden" name="acao" value="<?= (int) $usuario['ativo'] ? 'desativar' : 'reativar' ?>">
                                    <button class="usuarios-acao" type="submit"><?= (int) $usuario['ativo'] ? 'Desativar' : 'Reativar' ?></button>
                                </form>
                            <?php else: ?>
                                <span aria-label="Conta administrativa">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>