<?php
require_once __DIR__ . '/../connect/conexao.php';

function tabelaExiste($con, $nome)
{
    $nome = mysqli_real_escape_string($con, $nome);
    $res = mysqli_query($con, "SHOW TABLES LIKE '$nome'");
    return $res && mysqli_num_rows($res) > 0;
}

function moeda($valor)
{
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}

$temVendas = tabelaExiste($con, 'venda') && tabelaExiste($con, 'itemvenda');
$pagina = max(1, (int) ($_GET['page'] ?? 1));
$porPagina = 20;
$totalVendas = 0;
$linhas = [];

if ($temVendas) {
    $contagem = mysqli_query($con, 'SELECT COUNT(*) AS total FROM venda');
    $totalVendas = (int) (mysqli_fetch_assoc($contagem)['total'] ?? 0);
    $totalPaginas = max(1, (int) ceil($totalVendas / $porPagina));
    $pagina = min($pagina, $totalPaginas);
    $offset = ($pagina - 1) * $porPagina;

    $vendas = mysqli_query($con, "
         SELECT v.id_venda, v.valortotal, v.datavenda, v.numrecib, v.formapag,
             v.cliente_nome AS cliente
        FROM venda v
        ORDER BY v.datavenda DESC, v.id_venda DESC
        LIMIT $porPagina OFFSET $offset
    ");

    while ($venda = $vendas ? mysqli_fetch_assoc($vendas) : null) {
        $linhas[] = ['venda' => $venda, 'itens' => []];
    }

    if ($linhas) {
        $idsVendas = array_map(
            static fn ($linha) => (int) $linha['venda']['id_venda'],
            $linhas
        );
        $idsSql = implode(',', $idsVendas);
        $itensRes = mysqli_query($con, "
            SELECT iv.id_venda, iv.nome AS nome_item, iv.peso, iv.preco
            FROM itemvenda iv
            WHERE iv.id_venda IN ($idsSql)
            ORDER BY iv.id_venda, iv.id_itemvenda ASC
        ");
        $itensPorVenda = [];

        while ($item = $itensRes ? mysqli_fetch_assoc($itensRes) : null) {
            $peso = (float) ($item['peso'] ?? 0);
            $preco = (float) ($item['preco'] ?? 0);
            $itensPorVenda[(int) $item['id_venda']][] = [
                'produto' => $item['nome_item'] ?: 'Produto não informado',
                'peso' => $peso,
                'preco' => $preco,
                'subtotal' => $peso * $preco,
            ];
        }

        foreach ($linhas as &$linha) {
            $id = (int) $linha['venda']['id_venda'];
            $linha['itens'] = $itensPorVenda[$id] ?? [];
        }
        unset($linha);
    }
} else {
    $totalPaginas = 1;
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Histórico de Vendas</title>
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/historico.css">
</head>
<body>
<?php $paginaAtiva = 'historico'; require_once 'sidebar.php'; ?>
<main class="historico-container">
    <div class="historico-header"><h2>Histórico de Vendas</h2></div>
    <div class="botoes-exportacao"><a class="btn-html" href="?export=html">Abrir como HTML</a><a class="btn-pdf" href="?export=html">Imprimir / Salvar PDF</a></div>
    <div class="tabela-box">
        <table class="tabela-vendas">
            <thead><tr><th>ID</th><th>Cliente</th><th>Total</th><th>Data</th><th>Pagamento</th><th>Recibo</th><th>Ação</th></tr></thead>
            <tbody>
            <?php if ($linhas): foreach ($linhas as $linha): $venda = $linha['venda']; $id = (int) $venda['id_venda']; ?>
                <tr>
                    <td><?= $id ?></td>
                    <td><?= htmlspecialchars($venda['cliente'] ?? 'Cliente não informado') ?></td>
                    <td><?= moeda($venda['valortotal']) ?></td>
                    <td><?= htmlspecialchars($venda['datavenda'] ?? '') ?></td>
                    <td><?= htmlspecialchars($venda['formapag'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($venda['numrecib'] ?? '-') ?></td>
                    <td><a class="btn-ver" href="#venda-<?= $id ?>">Ver</a></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="7" class="tabela-vazia">Nenhuma venda encontrada.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php foreach ($linhas as $linha): $venda = $linha['venda']; $id = (int) $venda['id_venda']; ?>
    <div class="modal" id="venda-<?= $id ?>">
        <div class="modal-conteudo">
            <div class="modal-header"><h3>Detalhes da venda #<?= $id ?></h3><a class="btn-fechar" href="#" aria-label="Fechar">&times;</a></div>
            <div class="modal-body">
                <div class="informacoes-venda">
                    <div class="informacao"><span class="informacao-label">Cliente</span><span class="informacao-valor"><?= htmlspecialchars($venda['cliente'] ?? 'Cliente não informado') ?></span></div>
                    <div class="informacao"><span class="informacao-label">Data</span><span class="informacao-valor"><?= htmlspecialchars($venda['datavenda'] ?? '-') ?></span></div>
                    <div class="informacao"><span class="informacao-label">Total</span><span class="informacao-valor"><?= moeda($venda['valortotal']) ?></span></div>
                    <div class="informacao"><span class="informacao-label">Pagamento</span><span class="informacao-valor"><?= htmlspecialchars($venda['formapag'] ?? '-') ?></span></div>
                    <div class="informacao"><span class="informacao-label">Número do recibo</span><span class="informacao-valor"><?= htmlspecialchars($venda['numrecib'] ?? '-') ?></span></div>
                </div>
                <h4 class="titulo-itens">Produtos da venda</h4>
                <table class="tabela-itens"><thead><tr><th>Produto</th><th>Quantidade</th><th>Preço / kg</th><th>Subtotal</th></tr></thead><tbody>
                <?php if ($linha['itens']): foreach ($linha['itens'] as $item): ?><tr><td><?= htmlspecialchars($item['produto']) ?></td><td><?= number_format($item['peso'], 3, ',', '.') ?> kg</td><td><?= moeda($item['preco']) ?></td><td><?= moeda($item['subtotal']) ?></td></tr><?php endforeach; else: ?><tr><td colspan="4">Nenhum produto encontrado nesta venda.</td></tr><?php endif; ?>
                </tbody></table>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if ($totalPaginas > 1): ?><div class="paginacao">
        <?php if ($pagina > 1): ?><a href="?page=<?= $pagina - 1 ?>">&lsaquo;</a><?php endif; ?>
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?><a class="<?= $i === $pagina ? 'ativa' : '' ?>" href="?page=<?= $i ?>"><?= $i ?></a><?php endfor; ?>
        <?php if ($pagina < $totalPaginas): ?><a href="?page=<?= $pagina + 1 ?>">&rsaquo;</a><?php endif; ?>
    </div><?php endif; ?>
</main>
</body>
</html>
