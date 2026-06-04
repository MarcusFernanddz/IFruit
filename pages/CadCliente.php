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
        <a href="CadProd.php"><button type="button">Produtos</button></a>
        <a href="CadCliente.php"><button type="button" class="ativo">Cliente</button></a>
        <a href="Ajuste_C.php"><button type="button">Ajuste de Cliente</button></a>
        <a href="Ajuste_F.php"><button type="button">Ajuste de Produto</button></a>
    </div>

    <div class="bloco2fundo">
        <p>Novo Cliente</p>
        <form class="formulario" action="" method="POST">
            <input type="text"  name="nome"     placeholder="Nome do Cliente" required>
            <input type="text"  name="cpf"      placeholder="CPF"             required>
            <input type="email" name="email"    placeholder="Email"           required>
            <input type="text"  name="telefone" placeholder="Telefone"        required>
            <button type="submit" name="cadastrar">Cadastrar Cliente</button>
        </form>
    </div>

</main>

</body>
</html>
<?php
include __DIR__ . '/../connect/conexao.php';

if (isset($_POST['cadastrar'])) {
    $nome     = trim($_POST['nome']);
    $email    = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $cpf      = trim($_POST['cpf']);

    $sql = "INSERT INTO comprador(nome, cpf, email, telefone)
            VALUES ('$nome', '$cpf', '$email', '$telefone')";

    if (mysqli_query($conn, $sql)) {
        echo "Cliente cadastrado com sucesso!";
    } else {
        echo "Erro: " . mysqli_error($conn);
    }
}
?>
