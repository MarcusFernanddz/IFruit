<?php
<<<<<<< HEAD
$paginaAtiva = 'calculadora'; require_once 'sidebar.php'; include __DIR__ . '/../connect/conexao.php';
=======
$paginaAtiva = 'calculadora';
include __DIR__ . '/../connect/conexao.php';
>>>>>>> minhas-alteracoes

$itens = [];
$totalGeral = 0;
$erro = "";
<<<<<<< HEAD

if (isset($_POST['frutas']) && is_array($_POST['frutas'])) {

    $listaFrutas = $_POST['frutas'];
    $listaQtds   = $_POST['quantidade'];

    foreach ($listaFrutas as $i => $id_fruta) {

        $qtd = isset($listaQtds[$i]) ? $listaQtds[$i] : 0;

        if ($id_fruta === "" || $qtd === "" || $qtd <= 0) {
            continue;
        }

        $sql = "SELECT * FROM fruta WHERE id_fruta = '$id_fruta'";
=======
$numLinhas = max(1, min(8, intval($_POST['num_linhas'] ?? $_GET['linhas'] ?? 1)));

if (isset($_POST['adicionar_linha'])) {
    $numLinhas = min(8, $numLinhas + 1);
}
if (isset($_POST['remover_linha']) && $numLinhas > 1) {
    $removerLinha = intval($_POST['remover_linha']);
    if (isset($_POST['frutas']) && is_array($_POST['frutas'])) {
        array_splice($_POST['frutas'], $removerLinha, 1);
    }
    if (isset($_POST['quantidade']) && is_array($_POST['quantidade'])) {
        array_splice($_POST['quantidade'], $removerLinha, 1);
    }
    $numLinhas--;
}

// lista de frutas ordenada para o seletor HTML
$listaFrutas = mysqli_query($conn, "SELECT id_fruta, nome, precokg FROM fruta ORDER BY nome");

if (!isset($_POST['adicionar_linha']) && !isset($_POST['remover_linha']) && isset($_POST['frutas']) && is_array($_POST['frutas'])) {

    $postFrutas = $_POST['frutas'];
    // segurança: `quantidade` pode não estar presente ou não ser array
    if (isset($_POST['quantidade']) && is_array($_POST['quantidade'])) {
        $listaQtds = $_POST['quantidade'];
    } else {
        // preenche com zeros garantindo índices compatíveis
        $listaQtds = array_fill(0, count($postFrutas), 0);
    }

    foreach ($postFrutas as $i => $id_fruta) {

        $qtd = isset($listaQtds[$i]) ? floatval($listaQtds[$i]) : 0;

        if ($id_fruta === "" || $qtd <= 0) {
            continue;
        }

        $nomeFruta = mysqli_real_escape_string($conn, trim($id_fruta));
        $sql = "SELECT * FROM fruta WHERE nome = '$nomeFruta' LIMIT 1";
>>>>>>> minhas-alteracoes
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
<<<<<<< HEAD
    <link rel="stylesheet" href="../css/calculadora.css">
=======
    <link rel="stylesheet" href="../css/calculadora.css?v=2">
>>>>>>> minhas-alteracoes
</head>

<body>

<<<<<<< HEAD
=======
    <?php require_once 'sidebar.php'; ?>

>>>>>>> minhas-alteracoes
    <main class="main">

        <div class="calc-titulo">
            <p>Calculadora</p>
        </div>

        <div class="calc-card">
            <h2>Calcular conta</h2>

            <form method="post" id="form-calculadora">

                <div class="calc-linhas" id="calc-linhas">
<<<<<<< HEAD

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
=======
                    <?php for ($linha = 0; $linha < $numLinhas; $linha++): ?>
                    <div class="calc-linha">
                        <input type="search" name="frutas[]" list="frutas_disponiveis" class="calc-select-fruta-input pesquisa-datalist" placeholder="Pesquisar fruta..." value="<?= htmlspecialchars($_POST['frutas'][$linha] ?? '') ?>">
                        <button type="submit" name="remover_linha" value="<?= $linha ?>" class="calc-btn-remover" formnovalidate aria-label="Remover item">&times;</button>
                        <datalist id="frutas_disponiveis">
                                <?php
                                mysqli_data_seek($listaFrutas, 0);
                                while ($fr = mysqli_fetch_assoc($listaFrutas)):
                                    $precoFmt = number_format($fr['precokg'], 2, ',', '.');
                                ?>
                                    <option value="<?= htmlspecialchars($fr['nome']) ?>">R$ <?= $precoFmt ?>/kg</option>
                                <?php endwhile; ?>
                        </datalist>

                        <input type="number" step="0.01" name="quantidade[]" placeholder="Qtd (kg)" value="<?= htmlspecialchars($_POST['quantidade'][$linha] ?? '') ?>" min="0.01">
                    </div>
                    <?php endfor; ?>

                </div>

                <input type="hidden" name="num_linhas" value="<?= $numLinhas ?>">
                <?php if ($numLinhas < 8): ?>
                    <button type="submit" name="adicionar_linha" class="calc-btn-add">+ Adicionar item</button>
                <?php endif; ?>
>>>>>>> minhas-alteracoes

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

<<<<<<< HEAD
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

=======
>>>>>>> minhas-alteracoes
</body>
</html>