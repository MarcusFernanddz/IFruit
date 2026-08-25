<?php
include __DIR__ . '/../connect/conexao.php';

$mensagem = "";
$tipoMensagem = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['produto']);
    $pk   = trim($_POST['preco']);

    if ($nome === '') {
        $mensagem = "Informe o nome do produto.";
        $tipoMensagem = "erro";
    } elseif ($pk === '' || floatval(str_replace(',', '.', $pk)) < 0) {
        $mensagem = "Informe um preço válido.";
        $tipoMensagem = "erro";
    } else {
        $nomeEsc = mysqli_real_escape_string($conn, $nome);
        $pkEsc = mysqli_real_escape_string($conn, $pk);

        $verificaNome = mysqli_query($conn, "SELECT id_fruta FROM fruta WHERE LOWER(nome) = LOWER('$nomeEsc') LIMIT 1");

        if (mysqli_num_rows($verificaNome) > 0) {
            $mensagem = "Já existe um produto cadastrado com este nome.";
            $tipoMensagem = "erro";
        } else {
            $sql = "INSERT INTO fruta(nome, precokg) VALUES ('$nomeEsc', '$pkEsc')";

            if (mysqli_query($conn, $sql)) {
                $mensagem = "Produto cadastrado com sucesso!";
                $tipoMensagem = "sucesso";
            } else {
                if (strpos(mysqli_error($conn), 'Duplicate entry') !== false || strpos(mysqli_error($conn), 'nome_unique') !== false) {
                    $mensagem = "Não foi possível cadastrar: já existe um produto com este nome.";
                } else {
                    $mensagem = "Erro ao cadastrar produto: " . mysqli_error($conn);
                }
                $tipoMensagem = "erro";
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
<title>iFruit - Cadastros</title>
<link rel="stylesheet" href="../css/sidebar.css">
<link rel="stylesheet" href="../css/global.css">
</head>

<body>

<?php $paginaAtiva = 'cadastros'; require_once 'sidebar.php'; ?>

<main class="main">

    <div class="bloco1fundo">
        <p>Cadastros/Ajuste</p>
    </div>

    <div class="navbar">
        <a href="CadProd.php"><button type="button" class="ativo">Produtos</button></a>
        <a href="CadCliente.php"><button type="button">Cliente</button></a>
        <a href="Ajuste_C.php"><button type="button">Ajuste de Cliente</button></a>
        <a href="Ajuste_F.php"><button type="button">Ajuste de Produto</button></a>
    </div>

    <div class="bloco2fundo">
        <?php if (!empty($mensagem)): ?>
            <div class="mensagem <?= $tipoMensagem === 'erro' ? 'erro' : 'sucesso' ?>">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <p>Novo Produto</p>

        <form class="formulario" action="" method="POST">
            <input type="text"   name="produto" placeholder="Nome do Produto" required>
            <input type="number" name="preco"   placeholder="Preço" min="0" step="0.05" required>
            <button type="submit" name="cadastrar">Cadastrar Produto</button>
        </form>
    </div>

</main>

</body>
</html>
