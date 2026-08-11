<?php
include __DIR__ . '/../connect/conexao.php';

$mensagem     = "";
$tipoMensagem = "";
$frutaSelecionada = null;

/* ── ETAPA 2: salvar edição ── */
if (isset($_POST['salvar'])) {

    $id    = intval($_POST['id_fruta']);
    $nome  = trim($_POST['nome']);
    $preco = floatval(str_replace(',', '.', $_POST['precokg']));

    if ($nome === '') {
        $mensagem     = "O nome do produto não pode estar vazio.";
        $tipoMensagem = "erro";
        $frutaSelecionada = ['id_fruta' => $id, 'nome' => $nome, 'precokg' => $_POST['precokg']];
    } elseif ($preco < 0) {
        $mensagem     = "O preço não pode ser negativo.";
        $tipoMensagem = "erro";
        $frutaSelecionada = ['id_fruta' => $id, 'nome' => $nome, 'precokg' => $_POST['precokg']];
    } else {
        $nomeEsc = mysqli_real_escape_string($conn, $nome);
        $verificaNome = mysqli_query($conn, "SELECT id_fruta FROM fruta WHERE LOWER(nome) = LOWER('$nomeEsc') AND id_fruta != $id LIMIT 1");

        if (mysqli_num_rows($verificaNome) > 0) {
            $mensagem     = "Já existe outro produto cadastrado com este nome.";
            $tipoMensagem = "erro";
            $frutaSelecionada = ['id_fruta' => $id, 'nome' => $nome, 'precokg' => $_POST['precokg']];
        } else {
            $sql = "UPDATE fruta SET nome='$nomeEsc', precokg=$preco WHERE id_fruta=$id";

            if (mysqli_query($conn, $sql)) {
                $mensagem     = "Produto atualizado com sucesso!";
                $tipoMensagem = "sucesso";
            } else {
                if (strpos(mysqli_error($conn), 'Duplicate entry') !== false || strpos(mysqli_error($conn), 'nome_unique') !== false) {
                    $mensagem = "Não foi possível salvar: já existe outro produto com este nome.";
                } else {
                    $mensagem     = "Erro ao atualizar: " . mysqli_error($conn);
                }
                $tipoMensagem = "erro";
            }
        }
    }
}

/* ── ETAPA 1: carregar fruta para edição ── */
if (isset($_POST['selecionar'])) {
    $id  = intval($_POST['id_fruta']);
    $res = mysqli_query($conn, "SELECT * FROM fruta WHERE id_fruta=$id");
    $frutaSelecionada = mysqli_fetch_assoc($res);
}

/* ── lista de frutas para o select ── */
$listaFrutas = mysqli_query($conn, "SELECT id_fruta, nome, precokg FROM fruta ORDER BY nome");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iFruit - Ajuste de Produto</title>
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/global.css">
</head>
<body>

<?php $paginaAtiva = 'cadastros'; require_once 'sidebar.php'; ?>

<main class="main">

    <div class="bloco1fundo">
        <p>Cadastros / Ajuste de Produto</p>
    </div>

    <div class="navbar">
        <a href="CadProd.php">   <button type="button">Produtos</button></a>
        <a href="CadCliente.php"><button type="button">Cliente</button></a>
        <a href="Ajuste_C.php">  <button type="button">Ajuste de Cliente</button></a>
        <a href="Ajuste_F.php">  <button type="button" class="ativo">Ajuste de Produto</button></a>
    </div>

    <div class="bloco2fundo">
        <?php if (!empty($mensagem)): ?>
            <div class="mensagem <?= $tipoMensagem === 'erro' ? 'erro' : 'sucesso' ?>" style="display:block; width:min(100%,520px); margin:0 auto 16px; padding:10px 14px; font-size:13px; border-radius:8px; text-align:center;">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <p><?= $frutaSelecionada ? 'Editar Produto' : 'Selecionar Produto' ?></p>

        <!-- FORM DE SELEÇÃO -->
        <?php if (!$frutaSelecionada): ?>
        <form class="formulario" action="" method="POST">
            <select name="id_fruta" required
                    style="padding:12px; border-radius:6px; font-size:14px; outline:none; border:none;">
                <option value="">-- Escolha um produto --</option>
                <?php while ($f = mysqli_fetch_assoc($listaFrutas)): ?>
                    <?php $preco = number_format($f['precokg'], 2, ',', '.'); ?>
                    <option value="<?= $f['id_fruta'] ?>">
                        <?= htmlspecialchars($f['nome']) ?> — R$ <?= $preco ?>/kg
                    </option>
                <?php endwhile; ?>
            </select>

            <button type="submit" name="selecionar" style="width:200px;">
                Carregar Produto
            </button>
        </form>

        <!-- FORM DE EDIÇÃO -->
        <?php else: ?>
        <form class="formulario" action="" method="POST">
            <input type="hidden" name="id_fruta" value="<?= $frutaSelecionada['id_fruta'] ?>">

            <input type="text"
                   name="nome"
                   placeholder="Nome do produto"
                   value="<?= htmlspecialchars($frutaSelecionada['nome']) ?>"
                   required>

            <input type="number"
                   name="precokg"
                   placeholder="Preço por kg (R$)"
                   value="<?= htmlspecialchars($frutaSelecionada['precokg']) ?>"
                   min="0"
                   step="0.05"
                   required>

            <div style="display:flex; gap:10px;">
                <button type="submit" name="salvar" style="width:200px;">
                    Salvar Alterações
                </button>
                <a href="Ajuste_F.php">
                    <button type="button" style="width:160px; padding:12px; border:none; border-radius:6px; background:#888; color:#fff; font-weight:bold; cursor:pointer;">
                        Trocar Produto
                    </button>
                </a>
            </div>
        </form>
        <?php endif; ?>

    </div>

</main>
</body>
</html>