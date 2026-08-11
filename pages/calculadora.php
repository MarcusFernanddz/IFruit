<?php
$paginaAtiva = 'calculadora'; require_once 'sidebar.php'; include __DIR__ . '/../connect/conexao.php';

$itens = [];
$totalGeral = 0;
$erro = "";

if (isset($_POST['frutas']) && is_array($_POST['frutas'])) {

    $listaFrutas = $_POST['frutas'];
    $listaQtds   = $_POST['quantidade'];

    foreach ($listaFrutas as $i => $id_fruta) {

        $qtd = isset($listaQtds[$i]) ? $listaQtds[$i] : 0;

        if ($id_fruta === "" || $qtd === "" || $qtd <= 0) {
            continue;
        }

        $sql = "SELECT * FROM fruta WHERE id_fruta = '$id_fruta'";
        $result = mysqli_query($conn, $sql);

        if ($row = mysqli_fetch_assoc($result)) {
            $subtotal = $row['precokg'] * $qtd;
            $totalGeral += $subtotal;

            $itens[] = [
                'nome'     => $row['nome'],
                'precokg'  => $row['precokg'],
                'qtd'      => $qtd,
                'subtotal' => $subtotal,
            ];
        }
    }

    if (empty($itens)) {
        $erro = "Nenhum item válido foi informado.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculadora de Preços</title>
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/calculadora.css">
</head>

<body>

    <main class="main">

        <div class="calc-titulo">
            <p>Calculadora</p>
        </div>

        <div class="calc-card">
            <h2>Calcular conta</h2>

            <form method="post" id="form-calculadora">

                <div class="calc-linhas" id="calc-linhas">

                    <div class="calc-linha">
                        <select name="frutas[]" class="calc-select-fruta">
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
                        <input type="number" step="0.01" name="quantidade[]" placeholder="Qtd (kg)" required>
                        <button type="button" class="calc-btn-remover" onclick="removerLinha(this)">&times;</button>
                    </div>

                </div>

                <button type="button" class="calc-btn-add" onclick="adicionarLinha()">+ Adicionar item</button>

                <input type="submit" value="Calcular total">

            </form>

            <?php if ($erro): ?>
                <div class="calc-resultado calc-erro"><?= $erro ?></div>
            <?php elseif (!empty($itens)): ?>
                <div class="calc-resultado">
                    <ul class="calc-lista-itens">
                        <?php foreach ($itens as $item): ?>
                            <li>
                                <?= $item['nome'] ?> — <?= $item['qtd'] ?> kg
                                × R$ <?= number_format($item['precokg'], 2, ',', '.') ?>
                                = <strong>R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></strong>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="calc-total-geral">
                        Total geral: <strong>R$ <?= number_format($totalGeral, 2, ',', '.') ?></strong>
                    </div>
                </div>
            <?php endif; ?>

        </div>

    </main>

    <template id="template-linha">
        <div class="calc-linha">
            <select name="frutas[]" class="calc-select-fruta">
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
            <input type="number" step="0.01" name="quantidade[]" placeholder="Qtd (kg)" required>
            <button type="button" class="calc-btn-remover" onclick="removerLinha(this)">&times;</button>
        </div>
    </template>

    <script>
        function adicionarLinha() {
            const template = document.getElementById('template-linha');
            const clone = template.content.cloneNode(true);
            document.getElementById('calc-linhas').appendChild(clone);
        }

        function removerLinha(botao) {
            const linhas = document.querySelectorAll('#calc-linhas .calc-linha');
            if (linhas.length > 1) {
                botao.closest('.calc-linha').remove();
            }
        }
    </script>

</body>
</html>