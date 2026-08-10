<?php
$paginaAtiva = 'Calculadora'; require_once 'sidebar.php'; include __DIR__ . '/../connect/conexao.php';

if (isset($_POST['frutas'])) {
    $id_fruta = $_POST['frutas'];
    $qtd = $_POST['quantidade'];

    $sql = "SELECT * FROM fruta WHERE id_fruta = '$id_fruta'";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        echo "Nome da fruta: " . $row['nome'] . "<br>";
        echo "Preço por Quilograma: " . $row['precokg'] . "<br>";
        echo "Quantidade: " . $qtd . "<br>";
        echo "Total: " . ($row['precokg'] * $qtd) . "<br>";
    } else {
        echo "Fruta não encontrada.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculadora de Preços</title>
    <!-- <link rel="stylesheet" href="../css/sidebar.css"> -->
    <link rel="stylesheet" href="../css/global.css">
</head>

<body>

    <form method="post">

        <select name="frutas">
            <?php
            $sql = "SELECT * FROM fruta";
            $result = mysqli_query($conn, $sql);

            while ($fruta = mysqli_fetch_assoc($result)) {
                echo "<option value='" . $fruta['id_fruta'] . "'>" 
                   . $fruta['nome'] 
                   . "</option>";
            }
            ?>
        </select>
        <input type="number" name="quantidade" placeholder="Quantidade em kg" required>
        <input type="submit" value="Calcular">

    </form>


</body>
</html>