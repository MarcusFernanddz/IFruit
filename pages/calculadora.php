<?php
$paginaAtiva = 'calculadora';
include __DIR__ . '/../connect/conexao.php';

$itens = [];
$totalGeral = 0;
$erro = '';
$listaFrutas = mysqli_query($conn, 'SELECT id_fruta, nome, precokg FROM fruta ORDER BY nome');
$frutas = [];
while ($fruta = $listaFrutas ? mysqli_fetch_assoc($listaFrutas) : null) {
    $frutas[(int) $fruta['id_fruta']] = $fruta;
}
$numLinhas = max(1, min(8, (int) ($_POST['num_linhas'] ?? 1)));

if (isset($_POST['remover_linha'])) {
    $indiceRemover = max(0, (int) $_POST['remover_linha']);
    foreach (['frutas', 'quantidade'] as $campo) {
        if (isset($_POST[$campo]) && is_array($_POST[$campo])) {
            unset($_POST[$campo][$indiceRemover]);
            $_POST[$campo] = array_values($_POST[$campo]);
        }
    }
    $numLinhas = max(1, count($_POST['frutas'] ?? []));
} elseif (isset($_POST['adicionar_linha'])) {
    $numLinhas = min(8, $numLinhas + 1);
}

if (!isset($_POST['remover_linha']) && !isset($_POST['adicionar_linha']) && isset($_POST['frutas'], $_POST['quantidade']) && is_array($_POST['frutas']) && is_array($_POST['quantidade'])) {
    foreach ($_POST['frutas'] as $i => $idFruta) {
        $quantidade = (float) ($_POST['quantidade'][$i] ?? 0);
        $idFruta = (int) $idFruta;
        if ($idFruta <= 0 || $quantidade <= 0) {
            continue;
        }

        $fruta = $frutas[$idFruta] ?? null;
        if ($fruta) {
            $subtotal = (float) $fruta['precokg'] * $quantidade;
            $totalGeral += $subtotal;
            $itens[] = [
                'nome' => $fruta['nome'],
                'precokg' => $fruta['precokg'],
                'qtd' => $quantidade,
                'subtotal' => $subtotal,
            ];
        }
    }

    if (!$itens) {
        $erro = 'Nenhum item válido foi informado.';
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
<?php require_once 'sidebar.php'; ?>
<main class="main">
    <div class="calc-titulo"><p>Calculadora</p></div>
    <div class="calc-card">
        <h2>Calcular conta</h2>
        <form method="post" class="calc-form">
            <div class="calc-linhas">
                <?php for ($linha = 0; $linha < $numLinhas; $linha++): ?>
                <div class="calc-linha">
                    <select name="frutas[]" class="calc-select-fruta controle-formulario" required>
                        <option value="">Selecionar fruta</option>
                        <?php foreach ($frutas as $fruta): ?>
                            <option value="<?= (int) $fruta['id_fruta'] ?>" <?= (int) ($_POST['frutas'][$linha] ?? 0) === (int) $fruta['id_fruta'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($fruta['nome']) ?> - R$ <?= number_format($fruta['precokg'], 2, ',', '.') ?>/kg
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" step="0.01" min="0.01" name="quantidade[]" class="controle-formulario" placeholder="Qtd (kg)" value="<?= htmlspecialchars($_POST['quantidade'][$linha] ?? '') ?>" required>
                    <button type="submit" name="remover_linha" value="<?= $linha ?>" formnovalidate class="calc-btn-remover" aria-label="Excluir item" title="Excluir item">&times;</button>
                </div>
                <?php endfor; ?>
            </div>
            <input type="hidden" name="num_linhas" value="<?= $numLinhas ?>">
            <?php if ($numLinhas < 8): ?><button type="submit" name="adicionar_linha" formnovalidate class="calc-btn-add">+ Adicionar item</button><?php endif; ?>
            <input type="submit" value="Calcular total">
        </form>

        <?php if ($erro): ?>
            <div class="calc-resultado calc-erro"><?= htmlspecialchars($erro) ?></div>
        <?php elseif ($itens): ?>
            <div class="calc-resultado">
                <ul class="calc-lista-itens">
                    <?php foreach ($itens as $item): ?>
                        <li><?= htmlspecialchars($item['nome']) ?> - <?= $item['qtd'] ?> kg x R$ <?= number_format($item['precokg'], 2, ',', '.') ?> = <strong>R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></strong></li>
                    <?php endforeach; ?>
                </ul>
                <div class="calc-total-geral">Total geral: <strong>R$ <?= number_format($totalGeral, 2, ',', '.') ?></strong></div>
            </div>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
