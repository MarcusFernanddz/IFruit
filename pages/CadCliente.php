<?php
include __DIR__ . '/../connect/conexao.php';

$mensagem = "";
$tipoMensagem = "";

if (isset($_POST['cadastrar'])) {

    $nome     = trim($_POST['nome']);
    $email    = trim($_POST['email']);
    $telefone = preg_replace('/\D/', '', $_POST['telefone']);
    $cpf = preg_replace('/\D/', '', $_POST['cpf']);

    function validaCPF($cpf) {

        $cpf = preg_replace('/[^0-9]/is', '', $cpf);

        if (strlen($cpf) != 11) {
            return false;
        }

        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {

            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }

            $d = ((10 * $d) % 11) % 10;

            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }


    if (!validaCPF($cpf)) {

        $mensagem = "CPF inválido. Verifique os números digitados.";
        $tipoMensagem = "erro";

    } else {

        $sql = "INSERT INTO comprador(nome, cpf, email, telefone)
                VALUES ('$nome', '$cpf', '$email', '$telefone')";

        if (mysqli_query($conn, $sql)) {

            $mensagem = "Cliente cadastrado com sucesso!";
            $tipoMensagem = "sucesso";

        } else {

            $mensagem = "Erro ao cadastrar cliente: " . mysqli_error($conn);
            $tipoMensagem = "erro";
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
        <a href="CadProd.php"><button type="button">Produtos</button></a>
        <a href="CadCliente.php"><button type="button" class="ativo">Cliente</button></a>
        <a href="Ajuste_C.php"><button type="button">Ajuste de Cliente</button></a>
        <a href="Ajuste_F.php"><button type="button">Ajuste de Produto</button></a>
    </div>

    <div class="bloco2fundo">
        <p>Novo Cliente</p>
        <form class="formulario" action="" method="POST">
            <input type="text"  name="nome"     placeholder="Nome do Cliente" required>
            <input type="text" id="cpf" name="cpf" placeholder="CPF" maxlength="14" required> 
            <input type="email" name="email"    placeholder="Email"           required>
            <input type="text" id="telefone" name="telefone" placeholder="Telefone" maxlength="11" required>
            <button type="submit" name="cadastrar">Cadastrar Cliente</button>
            <?php if (!empty($mensagem)): ?>
            <div class="mensagem <?php echo $tipoMensagem; ?>">
                <?php echo $mensagem; ?>
            </div>
            <?php endif; ?>
        </form>
    </div>

</main>

</body>
</html>