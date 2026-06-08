<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>iFruit - Calculadora</title>
        <link rel="stylesheet" href="../css/sidebar.css">
        <link rel="stylesheet" href="../css/global.css">
        <link rel="stylesheet" href="../css/calculadora.css">
        <style>
            select {
                width: 300px;
                padding: 10px;
                font-size: 16px;
                border-radius: 6px;
            }
            .campo-produto {
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-width: 500px;
            }
        </style>
    </head>
        <body>
            <?php// $paginaAtiva = 'calculadora'; require_once 'sidebar.php';// ?>
            <?php include __DIR__ . '/../connect/conexao.php';?>
            
            <select id="produto-select">
                <option value="">Selecione um produto</option>
                <?php
                    $sql = "SELECT id_fruta, nome, precokg FROM fruta";
                    $result = mysqli_query($conn, $sql);
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<option value='" . $row['id_fruta'] . "'>" . $row['nome'] . " - R$ " . number_format($row['precokg'], 2, ',', '.') . "</option>";
                    }
                ?>
            </select>

            <label>Quantidade (kg):</label>
            <input type="number" id="quantidade" placeholder="Quantidade (kg)" min="0" step="0.05">

        </body>
    </html>