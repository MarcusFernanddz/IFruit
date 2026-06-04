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
<?php
include __DIR__ . '/../connect/conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['produto']);
    $pk   = trim($_POST['preco']);

    $sql = "INSERT INTO fruta(nome, precokg) VALUES ('$nome', '$pk')";

    if (mysqli_query($conn, $sql)) {
        echo "Produto cadastrado com sucesso!";
    } else {
        echo "Erro: " . mysqli_error($conn);
    }
}
?>
