<?php
require_once __DIR__ . '/../connect/conexao.php';

function table_exists($con, $name){
  $name = mysqli_real_escape_string($con, $name);
  $res = mysqli_query($con, "SHOW TABLES LIKE '".$name."'");
  return $res && mysqli_num_rows($res) > 0;
}

$hasSales = table_exists($con, 'venda') && table_exists($con, 'itemvenda');

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$export = $_GET['export'] ?? '';
if ($export === 'html' || $export === 'pdf') {
  // export full set (no pagination) as simple printable HTML
    if ($hasSales) {
    $all = mysqli_query($con, "SELECT v.id_venda AS id, v.id_comprador, v.valortotal AS total, v.datavenda AS created_at, c.nome as cliente FROM venda v LEFT JOIN comprador c ON c.id_comprador = v.id_comprador ORDER BY v.datavenda DESC");
    $rows = [];
    while ($r = mysqli_fetch_assoc($all)) $rows[] = $r;
  } else {
    $rows = [];
    $csvFile = __DIR__ . '/../data/sales.csv';
    if (file_exists($csvFile)) {
      $lines = file($csvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      $header = null;
      foreach ($lines as $i => $ln) {
        if ($i === 0) { $header = str_getcsv($ln); continue; }
        $cols = str_getcsv($ln);
        $rows[] = [
          'id' => $cols[0],
          'cliente' => $cols[1],
          'total' => $cols[2],
          'created_at' => $cols[3],
          'items_json' => $cols[4] ?? '[]'
        ];
      }
    }
  }

  echo "<!doctype html><html><head><meta charset=\"utf-8\"><title>Histórico de Vendas - Export</title>";
  echo "<style>body{font-family:Arial,Helvetica,sans-serif}table{width:100%;border-collapse:collapse}td,th{padding:8px;border:1px solid #ddd}</style>";
  echo "</head><body>";
  echo "<h2>Histórico de Vendas</h2>";
  echo "<table><thead><tr><th>Id</th><th>Cliente</th><th>Total</th><th>Data</th></tr></thead><tbody>";
  foreach ($rows as $s) {
    echo '<tr>';
    echo '<td>' . $s['id'] . '</td>';
    echo '<td>' . htmlspecialchars($s['cliente']) . '</td>';
    echo '<td>R$ ' . number_format(floatval($s['total']),2,',','.') . '</td>';
    echo '<td>' . $s['created_at'] . '</td>';
    echo '</tr>';
  }
  echo "</tbody></table>";
  if ($export === 'pdf') {
    echo "<style>@media print{body{margin:0}}</style>";
  }
  echo "</body></html>";
  exit;
}

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Histórico de Vendas</title>
  <link rel="stylesheet" href="/IFruit/css/global.css">
  <style>table{width:100%; border-collapse:collapse} td,th{padding:8px; border-bottom:1px solid #eee}</style>
</head>
<body>
<div class="container">
  <h2>Histórico de Vendas</h2>
  <a href="Venda.php">Registrar nova venda</a>
  <div style="margin-top:12px; margin-bottom:12px; display:flex; gap:8px;">
    <a href="?export=html" target="_blank" style="padding:8px 12px; background:#2d8f2d; color:#fff; border-radius:6px; text-decoration:none;">Abrir como HTML</a>
    <a href="?export=pdf" target="_blank" style="padding:8px 12px; background:#2196F3; color:#fff; border-radius:6px; text-decoration:none;">Imprimir / Salvar PDF</a>
  </div>
  <table>
    <thead><tr><th>Id</th><th>Cliente</th><th>Total</th><th>Data</th><th></th></tr></thead>
    <tbody>
    <?php
      // prepare rows for display: DB or CSV
      if ($hasSales) {
        $res = mysqli_query($con, "SELECT v.id_venda AS id, v.id_comprador, v.valortotal AS total, v.datavenda AS created_at, c.nome as cliente FROM venda v LEFT JOIN comprador c ON c.id_comprador = v.id_comprador ORDER BY v.datavenda DESC LIMIT $perPage OFFSET $offset");
        while($s = mysqli_fetch_assoc($res)){
    ?>
      <tr>
        <td><?= $s['id'] ?></td>
        <td><?= htmlspecialchars($s['cliente']) ?></td>
        <td>R$ <?= number_format($s['total'],2,',','.') ?></td>
        <td><?= $s['created_at'] ?></td>
        <td><a href="Historico.php?view=<?= $s['id'] ?>">Ver</a></td>
      </tr>
    <?php }
      } else {
        $rows = [];
        $csvFile = __DIR__ . '/../data/sales.csv';
        if (file_exists($csvFile)) {
          $lines = file($csvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
          foreach ($lines as $i => $ln) {
            if ($i === 0) continue;
            $cols = str_getcsv($ln);
            $rows[] = [
              'id' => $cols[0],
              'cliente' => $cols[1],
              'total' => $cols[2],
              'created_at' => $cols[3],
              'items_json' => $cols[4] ?? '[]'
            ];
          }
        }
        foreach ($rows as $s) {
    ?>
      <tr>
        <td><?= $s['id'] ?></td>
        <td><?= htmlspecialchars($s['cliente']) ?></td>
        <td>R$ <?= number_format(floatval($s['total']),2,',','.') ?></td>
        <td><?= $s['created_at'] ?></td>
        <td><a href="Historico.php?view=<?= $s['id'] ?>">Ver</a></td>
      </tr>
    <?php }
      }
    ?>
    </tbody>
  </table>

  <?php if(isset($_GET['view'])):
    $id = intval($_GET['view']);
    if ($hasSales) {
      $sres = mysqli_query($con, "SELECT * FROM venda WHERE id_venda = $id");
      $sale = mysqli_fetch_assoc($sres);
      $items = mysqli_query($con, "SELECT iv.*, f.nome FROM itemvenda iv LEFT JOIN fruta f ON f.id_fruta = iv.id_fruta WHERE iv.id_venda = $id");
    ?>
      <h3>Detalhes venda #<?= $id ?></h3>
      <div>Cliente: <?= htmlspecialchars($sale['id_comprador'] ?? $sale['id_comprador']) ?> — Total: R$ <?= number_format($sale['valortotal'],2,',','.') ?></div>
      <table>
        <thead><tr><th>Produto</th><th>Qtd</th><th>Preço</th><th>Subtotal</th></tr></thead>
        <tbody>
        <?php while($it = mysqli_fetch_assoc($items)): ?>
          <tr>
            <td><?= htmlspecialchars($it['nome']) ?></td>
            <td><?= $it['peso'] ?></td>
            <td>R$ <?= number_format($it['preco'],2,',','.') ?></td>
            <td>R$ <?= number_format($it['peso'] * $it['preco'],2,',','.') ?></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    <?php } else {
      // read from CSV
      $csvFile = __DIR__ . '/../data/sales.csv';
      $found = null;
      if (file_exists($csvFile)) {
        $lines = file($csvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $i => $ln) {
          if ($i === 0) continue;
          $cols = str_getcsv($ln);
          if (intval($cols[0]) === $id) { $found = $cols; break; }
        }
      }
      if ($found) {
        $itemsJson = $found[4] ?? '[]';
        $itemsArr = json_decode($itemsJson, true);
    ?>
      <h3>Detalhes venda #<?= $id ?></h3>
      <div>Cliente: <?= htmlspecialchars($found[1]) ?> — Total: R$ <?= number_format(floatval($found[2]),2,',','.') ?></div>
      <table>
        <thead><tr><th>Produto ID</th><th>Qtd</th><th>Preço</th><th>Subtotal</th></tr></thead>
        <tbody>
        <?php foreach($itemsArr as $it): ?>
          <tr>
            <td><?= htmlspecialchars($it['fruta_id']) ?></td>
            <td><?= $it['quantidade'] ?></td>
            <td>R$ <?= number_format($it['preco_unit'],2,',','.') ?></td>
            <td>R$ <?= number_format($it['subtotal'],2,',','.') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php } else { ?>
      <div>Nenhuma venda encontrada com esse id.</div>
    <?php }
    }
  endif; ?>

</div>
</body>
</html>
