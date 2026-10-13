<?php
require_once __DIR__ . '/../connect/auth.php';
exigirLogin();
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

function textoXmlExcel($valor)
{
    $valor = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string) $valor);
    return htmlspecialchars($valor, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function celulaTextoExcel($referencia, $valor, $estilo = '')
{
    $atributoEstilo = $estilo !== '' ? ' s="' . $estilo . '"' : '';
    return '<c r="' . $referencia . '" t="inlineStr"' . $atributoEstilo . '><is><t xml:space="preserve">'
        . textoXmlExcel($valor) . '</t></is></c>';
}

function celulaNumeroExcel($referencia, $valor, $casasDecimais, $estilo)
{
    $valor = number_format((float) $valor, $casasDecimais, '.', '');
    return '<c r="' . $referencia . '" s="' . $estilo . '"><v>' . $valor . '</v></c>';
}

function dataValida($valor)
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
        return false;
    }

    [$ano, $mes, $dia] = array_map('intval', explode('-', $valor));
    return checkdate($mes, $dia, $ano);
}

$filtros = [
    'cliente' => trim((string) ($_GET['cliente'] ?? '')),
    'ano' => trim((string) ($_GET['ano'] ?? '')),
    'mes' => trim((string) ($_GET['mes'] ?? '')),
    'data_inicio' => trim((string) ($_GET['data_inicio'] ?? '')),
    'data_fim' => trim((string) ($_GET['data_fim'] ?? '')),
    'pagamento' => trim((string) ($_GET['pagamento'] ?? '')),
];
$filtros['ano'] = preg_match('/^\d{4}$/', $filtros['ano']) ? $filtros['ano'] : '';
$filtros['mes'] = preg_match('/^(0?[1-9]|1[0-2])$/', $filtros['mes']) ? (int) $filtros['mes'] : '';
$filtros['data_inicio'] = dataValida($filtros['data_inicio']) ? $filtros['data_inicio'] : '';
$filtros['data_fim'] = dataValida($filtros['data_fim']) ? $filtros['data_fim'] : '';

$exportacao = $_GET['export'] ?? '';
$parametrosFiltro = array_filter($filtros, static fn ($valor) => $valor !== '');
$sufixoFiltro = http_build_query($parametrosFiltro);
$where = [];
if ($filtros['cliente'] !== '') {
    $where[] = "v.cliente_nome = '" . mysqli_real_escape_string($con, $filtros['cliente']) . "'";
}
if ($filtros['ano'] !== '') {
    $where[] = 'YEAR(v.datavenda) = ' . (int) $filtros['ano'];
}
if ($filtros['mes'] !== '') {
    $where[] = 'MONTH(v.datavenda) = ' . (int) $filtros['mes'];
}
if ($filtros['data_inicio'] !== '') {
    $where[] = "v.datavenda >= '" . $filtros['data_inicio'] . "'";
}
if ($filtros['data_fim'] !== '') {
    $where[] = "v.datavenda <= '" . $filtros['data_fim'] . "'";
}
if ($filtros['pagamento'] !== '') {
    $where[] = "v.formapag = '" . mysqli_real_escape_string($con, $filtros['pagamento']) . "'";
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$temVendas = tabelaExiste($con, 'venda') && tabelaExiste($con, 'itemvenda');
$pagina = max(1, (int) ($_GET['page'] ?? 1));
$porPagina = 20;
$totalVendas = 0;
$linhas = [];
$clientesFiltro = [];
$pagamentosFiltro = [];

if ($temVendas) {
    $clientesRes = mysqli_query($con, "SELECT DISTINCT cliente_nome FROM venda WHERE cliente_nome <> '' ORDER BY cliente_nome");
    while ($cliente = $clientesRes ? mysqli_fetch_assoc($clientesRes) : null) {
        $clientesFiltro[] = $cliente['cliente_nome'];
    }
    $pagamentosRes = mysqli_query($con, "SELECT DISTINCT formapag FROM venda WHERE formapag <> '' ORDER BY formapag");
    while ($pagamento = $pagamentosRes ? mysqli_fetch_assoc($pagamentosRes) : null) {
        $pagamentosFiltro[] = $pagamento['formapag'];
    }

    $contagem = mysqli_query($con, "SELECT COUNT(*) AS total FROM venda v $whereSql");
    $totalVendas = (int) (mysqli_fetch_assoc($contagem)['total'] ?? 0);
    $totalPaginas = max(1, (int) ceil($totalVendas / $porPagina));
    $pagina = min($pagina, $totalPaginas);
    $offset = ($pagina - 1) * $porPagina;
    $limiteSql = in_array($exportacao, ['html', 'pdf', 'venda', 'excel', 'xlsx'], true)
        ? ''
        : "LIMIT $porPagina OFFSET $offset";

    $vendas = mysqli_query($con, "
         SELECT v.id_venda, v.valortotal, v.datavenda, v.numrecib, v.formapag,
             v.cliente_nome AS cliente, v.registrado_por_nome, v.registrado_por_email
        FROM venda v
        $whereSql
        ORDER BY v.datavenda DESC, v.id_venda DESC
        $limiteSql
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

$linhasExportacao = $linhas;
if ($exportacao === 'venda') {
    $idVendaExportacao = (int) ($_GET['id'] ?? 0);
    $linhasExportacao = array_values(array_filter(
        $linhas,
        static fn ($linha) => (int) $linha['venda']['id_venda'] === $idVendaExportacao
    ));
}

if ($exportacao === 'excel' || $exportacao === 'xlsx') {
    $cabecalhos = [
        'ID venda', 'Cliente', 'Data', 'Forma de pagamento', 'Recibo',
        'Produto', 'Quantidade (kg)', 'Preço por kg', 'Subtotal', 'Total da venda',
        'Registrado por', 'E-mail do registrador',
    ];
    $xmlLinhas = [];
    $xmlCabecalho = [];
    foreach ($cabecalhos as $indice => $cabecalho) {
        $coluna = chr(ord('A') + $indice);
        $xmlCabecalho[] = celulaTextoExcel($coluna . '1', $cabecalho, '4');
    }
    $xmlLinhas[] = '<row r="1" ht="24" customHeight="1">' . implode('', $xmlCabecalho) . '</row>';

    $numeroLinha = 1;
    $fusoUtc = new DateTimeZone('UTC');
    $epocaExcel = new DateTimeImmutable('1899-12-30', $fusoUtc);
    foreach ($linhasExportacao as $linha) {
        $venda = $linha['venda'];
        $itens = $linha['itens'] ?: [[
            'produto' => 'Nenhum produto encontrado',
            'peso' => 0,
            'preco' => 0,
            'subtotal' => 0,
        ]];

        foreach ($itens as $item) {
            $numeroLinha++;
            $data = (string) ($venda['datavenda'] ?? '');
            $dataObjeto = dataValida($data)
                ? DateTimeImmutable::createFromFormat('!Y-m-d', $data, $fusoUtc)
                : false;
            $celulas = [
                celulaNumeroExcel('A' . $numeroLinha, $venda['id_venda'], 0, '0'),
                celulaTextoExcel('B' . $numeroLinha, $venda['cliente'] ?? 'Cliente não informado'),
                $dataObjeto
                    ? celulaNumeroExcel('C' . $numeroLinha, ($dataObjeto->getTimestamp() - $epocaExcel->getTimestamp()) / 86400, 0, '2')
                    : celulaTextoExcel('C' . $numeroLinha, $data),
                celulaTextoExcel('D' . $numeroLinha, $venda['formapag'] ?? '-'),
                celulaNumeroExcel('E' . $numeroLinha, $venda['numrecib'] ?? 0, 0, '0'),
                celulaTextoExcel('F' . $numeroLinha, $item['produto']),
                celulaNumeroExcel('G' . $numeroLinha, $item['peso'], 3, '1'),
                celulaNumeroExcel('H' . $numeroLinha, $item['preco'], 2, '3'),
                celulaNumeroExcel('I' . $numeroLinha, $item['subtotal'], 2, '3'),
                celulaNumeroExcel('J' . $numeroLinha, $venda['valortotal'], 2, '3'),
                celulaTextoExcel('K' . $numeroLinha, $venda['registrado_por_nome'] ?? 'Não identificado'),
                celulaTextoExcel('L' . $numeroLinha, $venda['registrado_por_email'] ?? 'Não identificado'),
            ];
            $xmlLinhas[] = '<row r="' . $numeroLinha . '">' . implode('', $celulas) . '</row>';
        }
    }

    $planilhaXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
        . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/>'
        . '<selection pane="bottomLeft" activeCell="A2" sqref="A2"/></sheetView></sheetViews>'
        . '<sheetFormatPr defaultRowHeight="19"/><cols>'
        . '<col min="1" max="1" width="12" customWidth="1"/><col min="2" max="2" width="24" customWidth="1"/>'
        . '<col min="3" max="3" width="15" customWidth="1"/><col min="4" max="4" width="24" customWidth="1"/>'
        . '<col min="5" max="5" width="14" customWidth="1"/><col min="6" max="6" width="28" customWidth="1"/>'
        . '<col min="7" max="7" width="22" customWidth="1"/><col min="8" max="10" width="20" customWidth="1"/>'
        . '<col min="11" max="11" width="24" customWidth="1"/><col min="12" max="12" width="32" customWidth="1"/>'
        . '</cols><sheetData>' . implode('', $xmlLinhas) . '</sheetData>'
        . '<autoFilter ref="A1:L' . $numeroLinha . '"/></worksheet>';

    $tiposXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
        . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
        . '<Default Extension="xml" ContentType="application/xml"/>'
        . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
        . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
        . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
        . '</Types>';
    $relacoesRaizXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
        . '</Relationships>';
    $livroXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
        . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<sheets><sheet name="Vendas" sheetId="1" r:id="rId1"/></sheets></workbook>';
    $relacoesLivroXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
        . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
        . '</Relationships>';
    $estilosXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
        . '<numFmts count="3"><numFmt numFmtId="164" formatCode="#,##0.000"/>'
        . '<numFmt numFmtId="165" formatCode="dd/mm/yyyy"/><numFmt numFmtId="166" formatCode="#,##0.00"/></numFmts>'
        . '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font>'
        . '<font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font></fonts>'
        . '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
        . '<fill><patternFill patternType="solid"><fgColor rgb="FF0F3D2E"/><bgColor indexed="64"/></patternFill></fill></fills>'
        . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
        . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
        . '<cellXfs count="5"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
        . '<xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
        . '<xf numFmtId="165" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
        . '<xf numFmtId="166" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
        . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
        . '</cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';

    $arquivoTemporario = tempnam(sys_get_temp_dir(), 'ifruit_');
    if ($arquivoTemporario === false) {
        http_response_code(500);
        exit('Não foi possível criar a planilha.');
    }
    unlink($arquivoTemporario);
    $arquivoXlsx = $arquivoTemporario . '.xlsx';

    try {
        $planilha = new PharData($arquivoXlsx, 0, null, Phar::ZIP);
        $planilha['[Content_Types].xml'] = $tiposXml;
        $planilha['_rels/.rels'] = $relacoesRaizXml;
        $planilha['xl/workbook.xml'] = $livroXml;
        $planilha['xl/_rels/workbook.xml.rels'] = $relacoesLivroXml;
        $planilha['xl/styles.xml'] = $estilosXml;
        $planilha['xl/worksheets/sheet1.xml'] = $planilhaXml;
        unset($planilha);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="historico-vendas.xlsx"');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        readfile($arquivoXlsx);
    } catch (Throwable $erro) {
        error_log('Falha ao gerar planilha de vendas: ' . $erro->getMessage());
        http_response_code(500);
        echo 'Não foi possível gerar a planilha.';
    }

    if (is_file($arquivoXlsx)) {
        unlink($arquivoXlsx);
    }
    exit;
}

if ($exportacao === 'html' || $exportacao === 'pdf' || $exportacao === 'venda') {
    $tituloExportacao = $exportacao === 'venda'
        ? 'Imprimir venda #' . ($idVendaExportacao ?? '')
        : ($exportacao === 'pdf' ? 'Imprimir histórico de vendas' : 'Histórico de vendas');
    ?>
    <!doctype html>
    <html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= htmlspecialchars($tituloExportacao) ?></title>
        <style>
            body { font-family: Arial, sans-serif; color: #222; margin: 32px; }
            h1 { color: #0f3d2e; margin-bottom: 24px; }
            .planilha { width: 100%; border-collapse: collapse; font-size: 12px; }
            th, td { border: 1px solid #bfc8c3; padding: 8px; text-align: left; }
            th { background: #0f3d2e; color: #fff; white-space: nowrap; }
            tbody tr:nth-child(even) { background: #f3f6f4; }
            .numero { text-align: right; white-space: nowrap; }
            .total-venda { font-weight: bold; background: #e2eee7; white-space: nowrap; }
            @media print {
                @page { size: landscape; margin: 10mm; }
                body { margin: 0; }
                .planilha { font-size: 10px; }
                tr { page-break-inside: avoid; }
            }
        </style>
    </head>
    <body>
        <h1>Histórico de Vendas</h1>
        <?php if ($linhasExportacao): ?>
            <table class="planilha">
                <thead><tr><th>ID Venda</th><th>Cliente</th><th>Data</th><th>Pagamento</th><th>Recibo</th><th>Produto</th><th>Quantidade</th><th>Preço / kg</th><th>Subtotal</th><th>Total da venda</th><th>Registrado por</th><th>E-mail do registrador</th></tr></thead>
                <tbody>
            <?php foreach ($linhasExportacao as $linha): $venda = $linha['venda']; $itens = $linha['itens'] ?: [['produto' => 'Nenhum produto encontrado', 'peso' => 0, 'preco' => 0, 'subtotal' => 0]]; ?>
                    <?php foreach ($itens as $item): ?>
                        <tr>
                            <td><?= (int) $venda['id_venda'] ?></td>
                            <td><?= htmlspecialchars($venda['cliente'] ?? 'Cliente não informado') ?></td>
                            <td><?= htmlspecialchars($venda['datavenda'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($venda['formapag'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($venda['numrecib'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($item['produto']) ?></td>
                            <td class="numero"><?= number_format($item['peso'], 3, ',', '.') ?> kg</td>
                            <td class="numero"><?= moeda($item['preco']) ?></td>
                            <td class="numero"><?= moeda($item['subtotal']) ?></td>
                            <td class="numero total-venda"><?= moeda($venda['valortotal']) ?></td>
                            <td><?= htmlspecialchars($venda['registrado_por_nome'] ?? 'Não identificado') ?></td>
                            <td><?= htmlspecialchars($venda['registrado_por_email'] ?? 'Não identificado') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhuma venda encontrada.</p>
        <?php endif; ?>
        <?php if ($exportacao === 'pdf' || $exportacao === 'venda'): ?><script>window.addEventListener('load', function () { window.print(); });</script><?php endif; ?>
    </body>
    </html>
    <?php
    exit;
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Histórico de Vendas</title>
    <link rel="stylesheet" href="../css/sidebar.css?v=2">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/historico.css?v=3">
</head>
<body>
<?php $paginaAtiva = 'historico'; require_once 'sidebar.php'; ?>
<main class="historico-container">
    <div class="historico-header"><h2>Histórico de Vendas</h2></div>
    <section class="painel-filtros">
        <div class="filtros-barra">
            <h3>Filtros</h3>
            <button class="btn-alternar-filtros" type="button" id="alternar-filtros" aria-label="Minimizar filtros" aria-expanded="true" aria-controls="formulario-filtros"></button>
        </div>
        <form class="filtros-historico" id="formulario-filtros" method="get">
        <label>Cliente
            <select name="cliente">
                <option value="">Todos os clientes</option>
                <?php foreach ($clientesFiltro as $cliente): ?>
                    <option value="<?= htmlspecialchars($cliente, ENT_QUOTES, 'UTF-8') ?>" <?= $filtros['cliente'] === $cliente ? 'selected' : '' ?>><?= htmlspecialchars($cliente) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Ano
            <input type="number" name="ano" min="2026" max="9999" placeholder="Todos" value="<?= htmlspecialchars($filtros['ano']) ?>">
        </label>
        <label>Mês
            <select name="mes">
                <option value="">Todos</option>
                <?php foreach (range(1, 12) as $mes): ?>
                    <option value="<?= $mes ?>" <?= (int) $filtros['mes'] === $mes ? 'selected' : '' ?>><?= str_pad((string) $mes, 2, '0', STR_PAD_LEFT) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Data inicial
            <input type="date" name="data_inicio" value="<?= htmlspecialchars($filtros['data_inicio']) ?>">
        </label>
        <label>Data final
            <input type="date" name="data_fim" value="<?= htmlspecialchars($filtros['data_fim']) ?>">
        </label>
        <label>Forma de pagamento
            <select name="pagamento">
                <option value="">Todas</option>
                <?php foreach ($pagamentosFiltro as $pagamento): ?>
                    <option value="<?= htmlspecialchars($pagamento, ENT_QUOTES, 'UTF-8') ?>" <?= $filtros['pagamento'] === $pagamento ? 'selected' : '' ?>><?= htmlspecialchars($pagamento) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="filtros-acoes">
            <button class="btn-html" type="submit">Filtrar</button>
            <a class="btn-limpar" href="Historico.php">Limpar</a>
        </div>
        </form>
    </section>
    <?php $queryFiltros = $sufixoFiltro !== '' ? '&amp;' . htmlspecialchars($sufixoFiltro, ENT_QUOTES, 'UTF-8') : ''; ?>
    <div class="botoes-exportacao"><a class="btn-html" href="?export=html<?= $queryFiltros ?>" target="_blank" rel="noopener">Abrir como HTML</a><a class="btn-pdf" href="?export=pdf<?= $queryFiltros ?>" target="_blank" rel="noopener">Imprimir / Salvar PDF</a><a class="btn-html" href="?export=xlsx<?= $queryFiltros ?>">Exportar para Excel (.xlsx)</a></div>
    <div class="tabela-box">
        <table class="tabela-vendas">
            <thead><tr><th>ID</th><th>Cliente</th><th>Total</th><th>Data</th><th>Pagamento</th><th>Recibo</th><th>Registrado por</th><th>Ação</th></tr></thead>
            <tbody>
            <?php if ($linhas): foreach ($linhas as $linha): $venda = $linha['venda']; $id = (int) $venda['id_venda']; ?>
                <tr>
                    <td><?= $id ?></td>
                    <td><?= htmlspecialchars($venda['cliente'] ?? 'Cliente não informado') ?></td>
                    <td><?= moeda($venda['valortotal']) ?></td>
                    <td><?= htmlspecialchars($venda['datavenda'] ?? '') ?></td>
                    <td><?= htmlspecialchars($venda['formapag'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($venda['numrecib'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($venda['registrado_por_nome'] ?? 'Não identificado') ?><br><small><?= htmlspecialchars($venda['registrado_por_email'] ?? '') ?></small></td>
                    <td><a class="btn-ver" href="#venda-<?= $id ?>">Ver</a></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="8" class="tabela-vazia">Nenhuma venda encontrada.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php foreach ($linhas as $linha): $venda = $linha['venda']; $id = (int) $venda['id_venda']; ?>
    <div class="modal" id="venda-<?= $id ?>">
        <div class="modal-conteudo">
            <div class="modal-header">
                <h3>Detalhes da venda #<?= $id ?></h3>
                <a class="btn-fechar" href="#" aria-label="Fechar">&times;</a>
            </div>
            <div class="modal-body">
                <div class="informacoes-venda">
                    <div class="informacao"><span class="informacao-label">Cliente</span><span class="informacao-valor"><?= htmlspecialchars($venda['cliente'] ?? 'Cliente não informado') ?></span></div>
                    <div class="informacao"><span class="informacao-label">Data</span><span class="informacao-valor"><?= htmlspecialchars($venda['datavenda'] ?? '-') ?></span></div>
                    <div class="informacao"><span class="informacao-label">Total</span><span class="informacao-valor"><?= moeda($venda['valortotal']) ?></span></div>
                    <div class="informacao"><span class="informacao-label">Pagamento</span><span class="informacao-valor"><?= htmlspecialchars($venda['formapag'] ?? '-') ?></span></div>
                    <div class="informacao"><span class="informacao-label">Número do recibo</span><span class="informacao-valor"><?= htmlspecialchars($venda['numrecib'] ?? '-') ?></span></div>
                    <div class="informacao"><span class="informacao-label">Registrado por</span><span class="informacao-valor"><?= htmlspecialchars($venda['registrado_por_nome'] ?? 'Não identificado') ?> (<?= htmlspecialchars($venda['registrado_por_email'] ?? '') ?>)</span></div>
                </div>
                <h4 class="titulo-itens">Produtos da venda</h4>
                <table class="tabela-itens"><thead><tr><th>Produto</th><th>Quantidade</th><th>Preço / kg</th><th>Subtotal</th></tr></thead><tbody>
                <?php if ($linha['itens']): foreach ($linha['itens'] as $item): ?><tr><td><?= htmlspecialchars($item['produto']) ?></td><td><?= number_format($item['peso'], 3, ',', '.') ?> kg</td><td><?= moeda($item['preco']) ?></td><td><?= moeda($item['subtotal']) ?></td></tr><?php endforeach; else: ?><tr><td colspan="4">Nenhum produto encontrado nesta venda.</td></tr><?php endif; ?>
                </tbody></table>
                <div class="modal-acoes"><a class="btn-ver btn-imprimir-modal" href="?export=venda&amp;id=<?= $id ?><?= $queryFiltros ?>" target="_blank" rel="noopener">Imprimir</a></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if ($totalPaginas > 1): ?><div class="paginacao">
        <?php if ($pagina > 1): ?><a href="?page=<?= $pagina - 1 ?><?= $queryFiltros ?>">&lsaquo;</a><?php endif; ?>
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?><a class="<?= $i === $pagina ? 'ativa' : '' ?>" href="?page=<?= $i ?><?= $queryFiltros ?>"><?= $i ?></a><?php endfor; ?>
        <?php if ($pagina < $totalPaginas): ?><a href="?page=<?= $pagina + 1 ?><?= $queryFiltros ?>">&rsaquo;</a><?php endif; ?>
    </div><?php endif; ?>
</main>
<script>
    const botaoFiltros = document.getElementById('alternar-filtros');
    const formularioFiltros = document.getElementById('formulario-filtros');

    botaoFiltros.addEventListener('click', function () {
        const expandido = botaoFiltros.getAttribute('aria-expanded') === 'true';
        formularioFiltros.hidden = expandido;
        botaoFiltros.setAttribute('aria-expanded', String(!expandido));
        botaoFiltros.setAttribute('aria-label', expandido ? 'Mostrar filtros' : 'Minimizar filtros');
    });

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape' && window.location.hash.startsWith('#venda-')) {
            window.location.hash = '';
        }
    });
</script>
</body>
</html>
