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
        <a href="CadCliente.php"><button type="button">Cliente</button></a>
        <a href="Ajuste_C.php"><button type="button" class="ativo">Ajuste de Cliente</button></a>
        <a href="Ajuste_F.php"><button type="button">Ajuste de Produto</button></a>
    </div>

    <div class="bloco2fundo">
        <p>Ajustar Cliente</p>
        <form class="formulario" action="" method="POST">
            <input type="text" name="produto"  placeholder="Nome do Cliente" required>
            <input type="text" name="CPF"      placeholder="CPF"             required>
            <input type="text" name="Email"    placeholder="Email"           required>
            <input type="text" name="Telefone" placeholder="Telefone"        required>
            <button type="submit">Ajustar Cliente</button>
        </form>
    </div>

</main>

</body>
</html>
