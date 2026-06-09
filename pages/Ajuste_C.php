<?php

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iFruit - Ajustes</title>
<link rel="stylesheet" href="../css/sidebar.css">
<link rel="stylesheet" href="../css/global.css">
</head>

<body>

<?php $paginaAtiva = 'cadastros'; require_once 'sidebar.php'; include __DIR__ . '/../connect/conexao.php';?>

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

    <?php

    function formatarTelefone($telefone) {
        if (strlen($telefone) == 11) {
            return '(' . substr($telefone, 0, 2) . ') ' .
                substr($telefone, 2, 5) . '-' .
                substr($telefone, 7, 4);
        }

        return $telefone;
    }

    function formatarCPF($cpf) {
        return substr($cpf, 0, 3) . '.' .
            substr($cpf, 3, 3) . '.' .
            substr($cpf, 6, 3) . '-' .
            substr($cpf, 9, 2);
    }

    $sql = mysqli_query($conn, "SELECT nome, cpf, email, telefone FROM comprador");

    while ($l = mysqli_fetch_assoc($sql)) {

        $nome = $l['nome'];
        $cpf = formatarCPF($l['cpf']);
        $email = $l['email'];
        $telefone = formatarTelefone($l['telefone']);

        echo "<h1>$nome</h1>";
        echo "<p><strong>CPF:</strong> $cpf</p>";
        echo "<p><strong>Email:</strong> $email</p>";
        echo "<p><strong>Telefone:</strong> $telefone</p>";
        echo "<button type='button'>Editar</button>";
    }
    ?>

</main>

</body>
</html>
