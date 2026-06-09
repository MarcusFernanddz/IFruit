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
                <a href="Ajuste_C.php"><button type="button">Ajuste de Cliente</button></a>
                <a href="Ajuste_F.php"><button type="button" class="ativo">Ajuste de Produto</button></a>
            </div>

            <?php
                $sql = mysqli_query($conn, "SELECT id_fruta, nome, precokg FROM fruta");
                    while ($l=mysqli_fetch_array($sql)){
                        $nome = $l['nome'];
                        $precokg = $l['precokg'];

                        echo "<h1>\n$nome</h1>";
                        echo "<p><strong>Preço por kg:</strong> R$" . number_format($precokg, 2, ',', '.') . "</p>";
                        echo "<button type=\"button\">Editar</button>";
                    }
 
            ?>

        </main>


        

        

    </body>
</html>
<?php
/*include __DIR__ . '/../connect/conexao.php';

$sql = mysqli_query($conn, "SELECT id_fruta, nome, precokg FROM fruta");
    while ($l=mysqli_fetch_array($sql)){
        $nome = $l['nome'];
        $precokg = $l['precokg'];

        echo "<strong>Nome:</strong> $nome <br>";
        echo "<strong>Preço por kg:</strong> $precokg <br>";
         }*/
?>
