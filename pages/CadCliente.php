<?php
include __DIR__ . '/../connect/conexao.php';

$mensagem = '';
$tipoMensagem = '';

function validaCPF($cpf)
{
    $cpf = preg_replace('/\D/', '', $cpf);
    if (strlen($cpf) !== 11 || preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }

    for ($t = 9; $t < 11; $t++) {
        $soma = 0;
        for ($c = 0; $c < $t; $c++) {
            $soma += $cpf[$c] * (($t + 1) - $c);
        }
        $digito = ((10 * $soma) % 11) % 10;
        if ((int) $cpf[$c] !== $digito) {
            return false;
        }
    }
    return true;
}

if (isset($_POST['cadastrar'])) {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = preg_replace('/\D/', '', $_POST['telefone'] ?? '');
    $cpf = preg_replace('/\D/', '', $_POST['cpf'] ?? '');

    if ($nome === '') {
        $mensagem = 'Informe o nome do cliente.';
        $tipoMensagem = 'erro';
    } elseif (!validaCPF($cpf)) {
        $mensagem = 'CPF inválido. Verifique os números digitados.';
        $tipoMensagem = 'erro';
    } else {
        $nomeEsc = mysqli_real_escape_string($conn, $nome);
        $emailEsc = mysqli_real_escape_string($conn, $email);
        $telefoneEsc = mysqli_real_escape_string($conn, $telefone);
        $cpfEsc = mysqli_real_escape_string($conn, $cpf);
        $verifica = mysqli_query($conn, "SELECT id_comprador FROM comprador WHERE cpf = '$cpfEsc' LIMIT 1");

        if ($verifica && mysqli_num_rows($verifica) > 0) {
            $mensagem = 'Já existe um cliente cadastrado com este CPF.';
            $tipoMensagem = 'erro';
        } elseif (mysqli_query($conn, "INSERT INTO comprador(nome, cpf, email, telefone) VALUES ('$nomeEsc', '$cpfEsc', '$emailEsc', '$telefoneEsc')")) {
            $mensagem = 'Cliente cadastrado com sucesso!';
            $tipoMensagem = 'sucesso';
        } else {
            $mensagem = 'Erro ao cadastrar cliente: ' . mysqli_error($conn);
            $tipoMensagem = 'erro';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iFruit - Cadastros</title>
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/cadastros.css">
</head>
<body>
<?php $paginaAtiva = 'cadastros'; require_once 'sidebar.php'; ?>
<main class="main">
    <div class="bloco1fundo"><p>Cadastros/Ajuste</p></div>
    <div class="navbar">
        <a href="CadProd.php"><button type="button">Produtos</button></a>
        <a href="CadCliente.php"><button type="button" class="ativo">Cliente</button></a>
        <a href="Ajuste_C.php"><button type="button">Ajuste de Cliente</button></a>
        <a href="Ajuste_F.php"><button type="button">Ajuste de Produto</button></a>
    </div>
    <div class="bloco2fundo">
        <?php if ($mensagem): ?><div class="mensagem <?= $tipoMensagem === 'erro' ? 'erro' : 'sucesso' ?>"><?= htmlspecialchars($mensagem) ?></div><?php endif; ?>
        <p>Novo Cliente</p>
        <form class="formulario" method="POST">
            <input type="text" name="nome" placeholder="Nome completo" required>
            <input type="text" name="cpf" placeholder="CPF" required>
            <input type="email" name="email" placeholder="E-mail">
            <input type="text" name="telefone" placeholder="Telefone">
            <button type="submit" name="cadastrar">Cadastrar Cliente</button>
        </form>
    </div>
</main>
</body>
</html>
