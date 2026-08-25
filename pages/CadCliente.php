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

<<<<<<< HEAD
        $sql = "INSERT INTO comprador(nome, cpf, email, telefone)
                VALUES ('$nome', '$cpf', '$email', '$telefone')";

        if (mysqli_query($conn, $sql)) {

            $mensagem = "Cliente cadastrado com sucesso!";
            $tipoMensagem = "sucesso";

        } else {

            $mensagem = "Erro ao cadastrar cliente: " . mysqli_error($conn);
            $tipoMensagem = "erro";
=======
        $nomeEsc = mysqli_real_escape_string($conn, $nome);
        $emailEsc = mysqli_real_escape_string($conn, $email);
        $telefoneEsc = mysqli_real_escape_string($conn, $telefone);
        $cpfEsc = mysqli_real_escape_string($conn, $cpf);

        $verifica = mysqli_query($conn, "SELECT id_comprador FROM comprador WHERE cpf = '$cpfEsc' LIMIT 1");

        if (mysqli_num_rows($verifica) > 0) {

            $mensagem = "Já existe um cliente cadastrado com este CPF.";
            $tipoMensagem = "erro";

        } else {

            $sql = "INSERT INTO comprador(nome, cpf, email, telefone)
                    VALUES ('$nomeEsc', '$cpfEsc', '$emailEsc', '$telefoneEsc')";

            try {
                if (mysqli_query($conn, $sql)) {

                    $mensagem = "Cliente cadastrado com sucesso!";
                    $tipoMensagem = "sucesso";

                } else {

                    $mensagem = "Erro ao cadastrar cliente: " . mysqli_error($conn);
                    $tipoMensagem = "erro";
                }
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() == 1062) {
                    $mensagem = "Não foi possível cadastrar: já existe um cliente com este CPF.";
                } else {
                    $mensagem = "Erro ao cadastrar cliente: " . $e->getMessage();
                }
                $tipoMensagem = "erro";
            }
>>>>>>> minhas-alteracoes
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD
<title>iFruit - Cadastros</title>
<link rel="stylesheet" href="../css/sidebar.css">
<link rel="stylesheet" href="../css/global.css">

=======

<title>iFruit - Cadastros</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI', Arial, sans-serif;
}

body{
    background:#f3f5f4;
    display:flex;
    min-height:100vh;
}

/* SIDEBAR */
.sidebar{
    width:250px;
    min-height:100vh;
    background:#0f3d2e;
    border-right:3px solid #1ed760;
    position:fixed;
    top:0;
    left:0;
    display:flex;
    flex-direction:column;
    z-index:100;
    box-shadow:3px 0 10px rgba(0,0,0,0.08);
}

/* LOGO */
.logo-area{
    padding:16px 12px;
    border-bottom:1px solid rgba(255,255,255,0.1);
    display:flex;
    align-items:center;
    justify-content:center;
    background:#0b3024;
}

.logo-img{
    width:150px;
    height:55px;
    object-fit:contain;
    filter:brightness(0) invert(1);
}

/* TEXTO */
.welcome{
    padding:16px 16px 8px;
    font-size:11px;
    color:#cde8d9;
}

.welcome strong{
    display:block;
    font-size:18px;
    font-weight:700;
    color:#1ed760;
    margin-top:2px;
}

/* MENU */
nav{
    padding:10px 0;
    flex:1;
}

.nav-item{
    margin:4px 8px;
    border-radius:10px;
    overflow:hidden;
    transition:all 0.2s ease;
    border-left:4px solid transparent;
}

.nav-item:hover{
    background:rgba(255,255,255,0.08);
    transform:translateX(4px);
    border-left-color:#1ed760;
}

.nav-item.active{
    background:rgba(30,215,96,0.15);
    border-left-color:#1ed760;
}

.nav-item a{
    text-decoration:none;
    color:inherit;
    display:block;
    padding:12px 14px;
}

.label{
    font-size:14px;
    font-weight:600;
    color:#fff;
}

.sublabel{
    font-size:11px;
    color:#b7d7c7;
    margin-top:2px;
}

/* CONTEÚDO */
.main{
    margin-left:250px;
    flex:1;
    padding:30px;
}

/* TOPO */
.bloco1fundo{
    width:100%;
    height:60px;
    background:#d9d9d9;
    display:flex;
    align-items:center;
    padding:0 25px;
    border-radius:10px;
    border-bottom:1px solid #bdbdbd;
    margin-bottom:30px;
}

.bloco1fundo p{
    font-size:22px;
    font-weight:bold;
    color:#0f3d2e;
}

/* NAVBAR */
.navbar{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    margin-bottom:30px;
}

.navbar a{
    text-decoration:none;
}

.navbar button{
    padding:10px 15px;
    border:none;
    background:#3a5f4c;
    color:white;
    border-radius:6px;
    cursor:pointer;
    transition:0.2s;
    font-weight:600;
}

.navbar button:hover{
    background:#2d4b3b;
    transform:translateY(-2px);
}

/* BOTÃO ATIVO */
.navbar button.ativo{
    background: #11c911c2;
    color:white;
    box-shadow:0 0 10px rgba(13, 158, 64, 0.4);
}

/* BLOCO PRINCIPAL */
.bloco2fundo{
    width:100%;
    max-width:800px;
    min-height:340px;
    background:#3a5f4c;
    border-radius:12px;
    padding:25px;
    color:white;
    box-shadow:0px 4px 10px rgba(0,0,0,0.2);
}

.bloco2fundo p{
    font-size:24px;
    font-weight:bold;
    margin-bottom:20px;
}

.mensagem {
    display:block;
    width:fit-content;
    max-width:560px;
    margin:0 auto 18px;
    padding:12px 16px;
    border-radius:8px;
    font-weight:700;
    font-size:14px;
    text-align:center;
    line-height:1.4;
    box-shadow:0 8px 20px rgba(0,0,0,0.12);
    border:1px solid transparent;
}

.erro {
    background-color:#ffe2e2;
    color:#9b1c1c;
    border-color:#f08a8a;
}

.sucesso {
    background-color:#e8fff1;
    color:#12693a;
    border-color:#4fd978;
}

/* FORM */
.formulario{
    display:flex;
    flex-direction:column;
    gap:15px;
}

.formulario input{
    padding:12px;
    border:none;
    border-radius:6px;
    outline:none;
    font-size:14px;
}

.formulario button{
    width:200px;
    padding:12px;
    border:none;
    border-radius:6px;
    background:#1ed760;
    color:#0f3d2e;
    font-weight:bold;
    cursor:pointer;
    transition:0.2s;
}

.formulario button:hover{
    background:#18b84f;
}

/* RESPONSIVO */
@media(max-width:768px){

    body{
        flex-direction:column;
    }

    .sidebar{
        width:100%;
        position:relative;
        min-height:auto;
    }

    .main{
        margin-left:0;
        padding:20px;
    }

    .navbar{
        flex-direction:column;
    }

    .navbar button{
        width:100%;
    }

    .bloco2fundo{
        width:100%;
    }
}

</style>
>>>>>>> minhas-alteracoes
</head>

<body>

<<<<<<< HEAD
<?php $paginaAtiva = 'cadastros'; require_once 'sidebar.php'; ?>

<main class="main">

    <div class="bloco1fundo">
        <p>Cadastros/Ajuste</p>
=======
<!-- SIDEBAR -->
<aside class="sidebar">

    <div class="logo-area">
        <img 
            class="logo-img"
            src="https://presencial.ifgoiano.edu.br/pluginfile.php/1/theme_mb2nl/logo/1777469559/Logo-Horizontal-Moodle%20%281%29.png"
            alt="Logo IF">
    </div>

    <div class="welcome">
        Seja bem-vindo ao
        <strong>iFruit</strong>
    </div>

    <nav>

        <div class="nav-item active">
            <a href="CadCliente.php">
                <div class="label">Cadastros/Ajuste</div>
                <div class="sublabel">Clientes e Frutas</div>
            </a>
        </div>

        <div class="nav-item">
            <a href="CadProd.php">
                <div class="label">Venda</div>
                <div class="sublabel">Realizar venda</div>
            </a>
        </div>

        <div class="nav-item">
            <a href="calculadora.php">
                <div class="label">Calculadora</div>
                <div class="sublabel">Teste de valores</div>
            </a>
        </div>

        <div class="nav-item">
            <a href="Ajuste_C.php">
                <div class="label">Histórico</div>
                <div class="sublabel">Vendas anteriores</div>
            </a>
        </div>

    </nav>

</aside>

<!-- CONTEÚDO -->
<main class="main">

    <!-- TOPO -->
    <div class="bloco1fundo">
        <p>Cadastros / Cliente</p>
>>>>>>> minhas-alteracoes
    </div>

    <div class="navbar">
        <a href="CadProd.php"><button type="button">Produtos</button></a>
        <a href="CadCliente.php"><button type="button" class="ativo">Cliente</button></a>
        <a href="Ajuste_C.php"><button type="button">Ajuste de Cliente</button></a>
        <a href="Ajuste_F.php"><button type="button">Ajuste de Produto</button></a>
    </div>

    <div class="bloco2fundo">
<<<<<<< HEAD
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
=======
        <?php if (!empty($mensagem)): ?>
            <div class="mensagem <?= $tipoMensagem === 'erro' ? 'erro' : 'sucesso' ?>">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>
        <p>Novo Cliente</p>

        <form class="formulario" action="" method="POST">
            <input type="text" name="nome" placeholder="Nome completo" required>
            <input type="text" name="cpf" placeholder="CPF" required>
            <input type="email" name="email" placeholder="E-mail">
            <input type="text" name="telefone" placeholder="Telefone">

            <button type="submit" name="cadastrar">
                Cadastrar Cliente
            </button>
>>>>>>> minhas-alteracoes
        </form>
    </div>

</main>

</body>
</html>