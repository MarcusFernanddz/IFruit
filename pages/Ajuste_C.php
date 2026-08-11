<?php
include __DIR__ . '/../connect/conexao.php';

$mensagem     = "";
$tipoMensagem = "";
$clienteSelecionado = null;

/* ── ETAPA 2: salvar edição ── */
if (isset($_POST['salvar'])) {

    $id       = intval($_POST['id_comprador']);
    $nome     = trim($_POST['nome']);
    $email    = trim($_POST['email']);
    $telefone = preg_replace('/\D/', '', $_POST['telefone']);
    $cpf      = preg_replace('/\D/', '', $_POST['cpf']);

    function validaCPF($cpf) {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) return false;
        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) $d += $cpf[$c] * (($t + 1) - $c);
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) return false;
        }
        return true;
    }

    if (!validaCPF($cpf)) {
        $mensagem     = "CPF inválido. Verifique os números digitados.";
        $tipoMensagem = "erro";
        $clienteSelecionado = [
            'id_comprador' => $id,
            'nome'         => $nome,
            'cpf'          => $_POST['cpf'],
            'email'        => $email,
            'telefone'     => $_POST['telefone'],
        ];
    } else {
        $nome     = mysqli_real_escape_string($conn, $nome);
        $email    = mysqli_real_escape_string($conn, $email);
        $telefone = mysqli_real_escape_string($conn, $telefone);
        $cpf      = mysqli_real_escape_string($conn, $cpf);

        $verificaCpf = mysqli_query($conn, "SELECT id_comprador FROM comprador WHERE cpf = '$cpf' AND id_comprador != $id LIMIT 1");

        if (mysqli_num_rows($verificaCpf) > 0) {
            $mensagem     = "Já existe outro cliente cadastrado com este CPF.";
            $tipoMensagem = "erro";
            $clienteSelecionado = [
                'id_comprador' => $id,
                'nome'         => $nome,
                'cpf'          => $_POST['cpf'],
                'email'        => $email,
                'telefone'     => $_POST['telefone'],
            ];
        } else {
            $sql = "UPDATE comprador
                    SET nome='$nome', cpf='$cpf', email='$email', telefone='$telefone'
                    WHERE id_comprador=$id";

            if (mysqli_query($conn, $sql)) {
                $mensagem     = "Cliente atualizado com sucesso!";
                $tipoMensagem = "sucesso";
            } else {
                if (strpos(mysqli_error($conn), 'Duplicate entry') !== false || strpos(mysqli_error($conn), 'cpf') !== false) {
                    $mensagem = "Não foi possível salvar: já existe outro cliente com este CPF.";
                } else {
                    $mensagem     = "Erro ao atualizar: " . mysqli_error($conn);
                }
                $tipoMensagem = "erro";
            }
        }
    }
}

/* ── ETAPA 1: carregar cliente para edição ── */
if (isset($_POST['selecionar'])) {
    $id  = intval($_POST['id_comprador']);
    $res = mysqli_query($conn, "SELECT * FROM comprador WHERE id_comprador=$id");
    $clienteSelecionado = mysqli_fetch_assoc($res);
}

/* ── lista de clientes para o select ── */
$listaClientes = mysqli_query($conn, "SELECT id_comprador, nome, cpf FROM comprador ORDER BY nome");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iFruit - Ajuste de Cliente</title>
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/global.css">
</head>
<body>

<?php $paginaAtiva = 'cadastros'; require_once 'sidebar.php'; ?>

<main class="main">

    <div class="bloco1fundo">
        <p>Cadastros / Ajuste de Cliente</p>
    </div>

    <div class="navbar">
        <a href="CadProd.php">   <button type="button">Produtos</button></a>
        <a href="CadCliente.php"><button type="button">Cliente</button></a>
        <a href="Ajuste_C.php">  <button type="button" class="ativo">Ajuste de Cliente</button></a>
        <a href="Ajuste_F.php">  <button type="button">Ajuste de Produto</button></a>
    </div>

    <div class="bloco2fundo">
        <?php if (!empty($mensagem)): ?>
            <div class="mensagem <?= $tipoMensagem === 'erro' ? 'erro' : 'sucesso' ?>" style="display:block; width:min(100%,520px); margin:0 auto 16px; padding:10px 14px; font-size:13px; border-radius:8px; text-align:center;">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <p><?= $clienteSelecionado ? 'Editar Cliente' : 'Selecionar Cliente' ?></p>

        <!-- FORM DE SELEÇÃO -->
        <?php if (!$clienteSelecionado): ?>
        <form class="formulario" action="" method="POST">
            <select name="id_comprador" required
                    style="padding:12px; border-radius:6px; font-size:14px; outline:none; border:none;">
                <option value="">-- Escolha um cliente --</option>
                <?php while ($c = mysqli_fetch_assoc($listaClientes)): ?>
                    <?php
                        $cpfRaw = preg_replace('/\D/', '', $c['cpf']);
                        $cpfFmt = strlen($cpfRaw) == 11
                                ? substr($cpfRaw,0,3).'.'.substr($cpfRaw,3,3).'.'.substr($cpfRaw,6,3).'-'.substr($cpfRaw,9,2)
                                : $c['cpf'];
                    ?>
                    <option value="<?= $c['id_comprador'] ?>">
                        <?= htmlspecialchars($c['nome']) ?> — <?= $cpfFmt ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <button type="submit" name="selecionar" style="width:200px;">
                Carregar Cliente
            </button>
        </form>

        <!-- FORM DE EDIÇÃO -->
        <?php else:
            $cpfVal = preg_replace('/\D/', '', $clienteSelecionado['cpf']);
            $cpfFmt = strlen($cpfVal) == 11
                    ? substr($cpfVal,0,3).'.'.substr($cpfVal,3,3).'.'.substr($cpfVal,6,3).'-'.substr($cpfVal,9,2)
                    : $clienteSelecionado['cpf'];

            $telVal = preg_replace('/\D/', '', $clienteSelecionado['telefone']);
            $telFmt = strlen($telVal) == 11
                    ? '('.substr($telVal,0,2).') '.substr($telVal,2,5).'-'.substr($telVal,7,4)
                    : $clienteSelecionado['telefone'];
        ?>
        <form class="formulario" action="" method="POST">
            <input type="hidden" name="id_comprador" value="<?= $clienteSelecionado['id_comprador'] ?>">

            <input type="text"
                   name="nome"
                   placeholder="Nome completo"
                   value="<?= htmlspecialchars($clienteSelecionado['nome']) ?>"
                   required>

            <input type="text"
                   name="cpf"
                   placeholder="CPF (000.000.000-00)"
                   value="<?= htmlspecialchars($cpfFmt) ?>"
                   required>

            <input type="email"
                   name="email"
                   placeholder="E-mail"
                   value="<?= htmlspecialchars($clienteSelecionado['email']) ?>">

            <input type="text"
                   name="telefone"
                   placeholder="Telefone ((00) 00000-0000)"
                   value="<?= htmlspecialchars($telFmt) ?>">

            <div style="display:flex; gap:10px;">
                <button type="submit" name="salvar" style="width:200px;">
                    Salvar Alterações
                </button>
                <a href="Ajuste_C.php">
                    <button type="button" style="width:160px; padding:12px; border:none; border-radius:6px; background:#888; color:#fff; font-weight:bold; cursor:pointer;">
                        Trocar Cliente
                    </button>
                </a>
            </div>
        </form>
        <?php endif; ?>

    </div>

</main>
</body>
</html>